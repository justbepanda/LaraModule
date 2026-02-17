<?php

namespace Modules\Task\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Task\Dto\CreateTaskDto;
use Modules\Task\Dto\UpdateTaskDto;
use Modules\Task\Events\TaskCreatedEvent;
use Modules\Task\Repositories\TaskRepository;
use Modules\Task\Models\Task;
use Modules\Task\Dto\GetTasksDto;
use Modules\Technique\Services\TechniqueService;

class TaskService
{
    /**
     * @param TaskRepository $taskRepository
     * @param TechniqueService $techniqueService
     */
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly TechniqueService $techniqueService,
    )
    {
    }

    /**
     * @param CreateTaskDto $params
     * @return Task
     */
    public function create(CreateTaskDto $params): Task
    {
        $task = $this->taskRepository->create($params);

        // Обновление пробега техники из задачи
        if ($params->techniqueId) {
            $technique = $this->techniqueService->getById($params->techniqueId);

            $this->techniqueService->updateMileage(
                $technique,
                $params->mileageValue,
                $params->mileageType,
            );
        }

        event(new TaskCreatedEvent($task));

        return $task;
    }

    /**
     * @param GetTasksDto $params
     * @return LengthAwarePaginator
     */
    public function get(GetTasksDto $params): LengthAwarePaginator
    {
        return $this->taskRepository->get($params);
    }

    /**
     * @param Task $task
     * @param UpdateTaskDto $params
     * @return Task
     */
    public function update(Task $task, UpdateTaskDto $params): Task
    {
        $task = $this->taskRepository->update($task, $params);

        // Обновление пробега техники из задачи
        if ($params->techniqueId) {
            $technique = $this->techniqueService->getById($params->techniqueId);

            $this->techniqueService->updateMileage(
                $technique,
                $params->mileageValue,
                $params->mileageType,
            );
        }

        return $task;
    }

    /**
     * @param string $id
     * @return Task
     */
    public function getById(string $id): Task
    {
        return $this->taskRepository->getById($id);
    }
}