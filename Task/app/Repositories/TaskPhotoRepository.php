<?php

namespace Modules\Task\Repositories;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Task\Dto\UploadTaskPhotoDto;
use Modules\Task\Models\Task;
use Modules\Task\Models\TaskPhoto;
use Throwable;

/**
 * Документы для задач.
 * Репозиторий.
 */
class TaskPhotoRepository
{
    protected const string DISK = 'public';

    /**
     * Сохраняет массив фотографий для задачи
     *
     * @param Task $task
     * @param UploadTaskPhotoDto $dto
     * @return TaskPhoto[]
     * @throws Throwable
     */
    public function storeMany(Task $task, UploadTaskPhotoDto $dto): array
    {
        $photos = $dto->photos;
        $taskId = $task->id;

        if (empty($photos)) {
            return [];
        }

        return DB::transaction(function () use ($taskId, $photos) {
            $result = [];

            foreach ($photos as $file) {
                if ($file instanceof UploadedFile) {
                    $result[] = $this->store($taskId, $file);
                }
            }

            return $result;
        });
    }

    /**
     * Сохраняет одно фото задачи.
     *
     * @param string $taskId
     * @param UploadedFile $file
     * @return TaskPhoto
     */
    public function store(string $taskId, UploadedFile $file): TaskPhoto
    {
        $folder = $this->getTaskFolderPath($taskId);

        $path = $file->store($folder, ['disk' => self::DISK]);

        return TaskPhoto::create([
            'task_id'  => $taskId,
            'name'     => $file->getClientOriginalName(),
            'path'     => $path,
        ]);
    }

    /**
     * Удаляет фото и файл.
     *
     * @param TaskPhoto $document
     * @return void
     */
    public function delete(TaskPhoto $document): void
    {
        if ($document->path) {
            Storage::disk(self::DISK)->delete($document->path);
        }

        $document->delete();
    }

    /**
     * Полная очистка фотографий задачи.
     */
    public function deleteByTask(string $taskId): void
    {
        $folder = $this->getTaskFolderPath($taskId);
        Storage::disk(self::DISK)->deleteDirectory($folder);
        TaskPhoto::where('task_id', $taskId)->delete();
    }

    /**
     * Формирование пути
     *
     * @param string $taskId
     * @return string
     */
    private function getTaskFolderPath(string $taskId): string
    {
        return "tasks/{$taskId}/photos";
    }

    /**
     * @param string $id
     * @return TaskPhoto
     */
    public function getById(string $id): TaskPhoto
    {
        return TaskPhoto::findOrFail($id);
    }
}