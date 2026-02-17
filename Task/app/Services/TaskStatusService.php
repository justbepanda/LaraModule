<?php

namespace Modules\Task\Services;

use Illuminate\Support\Collection;
use Modules\Task\Repositories\TaskStatusRepository;

/**
 * Статусы задач.
 * Сервис.
 */
class TaskStatusService
{
    /**
     * @param TaskStatusRepository $taskStatusRepository
     */
    public function __construct(
        private readonly TaskStatusRepository $taskStatusRepository
    ) {}

    /**
     * Синхронизировать статусы задач.
     *
     * @param Collection $statuses
     */
    public function sync(Collection $statuses): void
    {
        $this->taskStatusRepository->sync($statuses);
    }

    /**
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->taskStatusRepository->getAll();
    }
}
