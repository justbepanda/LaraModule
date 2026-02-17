<?php

namespace Modules\Task\Services;

use Modules\Task\Dto\UploadTaskDocumentDto;
use Modules\Task\Models\Task;
use Modules\Task\Models\TaskDocument;
use Modules\Task\Repositories\TaskDocumentRepository;
use Throwable;

/**
 * Документы для задач.
 * Сервис.
 */
class TaskDocumentService
{
    private TaskDocumentRepository $taskDocumentRepository;

    /**
     * @param TaskDocumentRepository $repository
     */
    public function __construct(TaskDocumentRepository $repository)
    {
        $this->taskDocumentRepository = $repository;
    }

    /**
     * Загружает документы для задачи.
     *
     * @param Task $task
     * @param UploadTaskDocumentDto $dto
     * @return TaskDocument[]
     * @throws Throwable
     */
    public function uploadDocuments(Task $task, UploadTaskDocumentDto $dto): array
    {
        $documents = $this->taskDocumentRepository->storeMany($task, $dto);
        return $documents;
    }
}