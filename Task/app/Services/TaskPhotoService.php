<?php

namespace Modules\Task\Services;

use Modules\Task\Dto\UploadTaskPhotoDto;
use Modules\Task\Models\Task;
use Modules\Task\Models\TaskPhoto;
use Modules\Task\Repositories\TaskPhotoRepository;
use Throwable;

/**
 * Фотографии для задач.
 * Сервис.
 */
class TaskPhotoService
{
    private TaskPhotoRepository $taskPhotoRepository;

    /**
     * @param TaskPhotoRepository $repository
     */
    public function __construct(TaskPhotoRepository $repository)
    {
        $this->taskPhotoRepository = $repository;
    }

    /**
     * Загружает фотографии для задачи.
     *
     * @param Task $task
     * @param UploadTaskPhotoDto $dto
     * @return TaskPhoto[]
     * @throws Throwable
     */
    public function uploadPhotos(Task $task, UploadTaskPhotoDto $dto): array
    {
        $photos = $this->taskPhotoRepository->storeMany($task, $dto);
        return $photos;
    }
}