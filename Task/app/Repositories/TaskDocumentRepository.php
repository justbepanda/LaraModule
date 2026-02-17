<?php

namespace Modules\Task\Repositories;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Task\Dto\UploadTaskDocumentDto;
use Modules\Task\Models\Task;
use Modules\Task\Models\TaskDocument;
use Throwable;

/**
 * Документы для задач.
 * Репозиторий.
 */
class TaskDocumentRepository
{
    protected const string DISK = 'public';

    /**
     * Сохраняет массив документов для задачи
     *
     * @param Task $task
     * @param UploadTaskDocumentDto $dto
     * @return TaskDocument[]
     * @throws Throwable
     */
    public function storeMany(Task $task, UploadTaskDocumentDto $dto): array
    {
        $documents = $dto->documents;
        $taskId = $task->id;

        if (empty($documents)) {
            return [];
        }

        return DB::transaction(function () use ($taskId, $documents) {
            $result = [];

            foreach ($documents as $file) {
                if ($file instanceof UploadedFile) {
                    $result[] = $this->store($taskId, $file);
                }
            }

            return $result;
        });
    }

    /**
     * Сохраняет один документ задачи.
     *
     * @param string $taskId
     * @param UploadedFile $file
     * @return TaskDocument
     */
    public function store(string $taskId, UploadedFile $file): TaskDocument
    {
        $folder = $this->getTaskFolderPath($taskId);

        $path = $file->store($folder, ['disk' => self::DISK]);

        return TaskDocument::create([
            'task_id'  => $taskId,
            'name'     => $file->getClientOriginalName(),
            'path'     => $path,
        ]);
    }

    /**
     * Удаляет документ и файл.
     *
     * @param TaskDocument $document
     * @return void
     */
    public function delete(TaskDocument $document): void
    {
        if ($document->path) {
            Storage::disk(self::DISK)->delete($document->path);
        }

        $document->delete();
    }

    /**
     * Полная очистка документов задачи.
     */
    public function deleteByTask(string $taskId): void
    {
        $folder = $this->getTaskFolderPath($taskId);
        Storage::disk(self::DISK)->deleteDirectory($folder);
        TaskDocument::where('task_id', $taskId)->delete();
    }

    /**
     * Формирование пути
     *
     * @param string $taskId
     * @return string
     */
    private function getTaskFolderPath(string $taskId): string
    {
        return "tasks/{$taskId}/documents";
    }

    /**
     * @param string $id
     * @return TaskDocument
     */
    public function getById(string $id): TaskDocument
    {
        return TaskDocument::findOrFail($id);
    }
}