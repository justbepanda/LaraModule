<?php

namespace Modules\Task\Repositories;

use Modules\Task\Dto\CreateTaskDto;
use Modules\Task\Dto\GetTasksDto;
use Modules\Task\Dto\UpdateTaskDto;
use Modules\Task\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TaskRepository
{
    /**
     * @param CreateTaskDto $params
     * @return Task
     */
    public function create(CreateTaskDto $params): Task
    {
        $task = Task::query()->create([
            'company_id' => $params->companyId,
            'task_type_id' => $params->taskTypeId,
            'technique_id' => $params->techniqueId,
            'address' => $params->address,
            'mileage_value' => $params->mileageValue,
            'mileage_type' => $params->mileageType->value,
            'description' => $params->description,
            'author_id' => $params->authorId,
        ]);

        return $task->load(['documents', 'photos', 'company', 'taskType', 'technique', 'taskStatus']);
    }

    /**
     * @param GetTasksDto $params
     * @return LengthAwarePaginator
     */
    public function get(GetTasksDto $params): LengthAwarePaginator
    {
        $task = Task::query()
            ->when($params->companyId, function (Builder $builder) use ($params) {
                $builder->where('company_id', $params->companyId);
            })
            ->when($params->taskTypeId, function (Builder $builder) use ($params) {
                $builder->where('task_type_id', $params->taskTypeId);
            })
            ->when($params->description || $params->vin || $params->registrationNumber, function (Builder $query) use ($params) {
                $query->where(function (Builder $query) use ($params) {
                    if ($params->description) {
                        $query->where('description', 'like', "%$params->description%");
                    }
                    if ($params->vin) {
                        $query->orWhereHas('technique', function (Builder $q) use ($params) {
                            $q->where('vin', 'like', "%$params->vin%");
                        });
                    }
                    if ($params->registrationNumber) {
                        $query->orWhereHas('technique', function (Builder $q) use ($params) {
                            $q->where('registration_number', 'like', "%$params->registrationNumber%");
                        });
                    }
                });
            })
            ->paginate(...$params->paginateDto->getDataForQuery());
        $task->load(['documents', 'photos', 'company', 'taskType', 'technique', 'taskStatus']);
        return $task;
    }

    /**
     * @param Task $task
     * @param UpdateTaskDto $params
     * @return Task
     */
    public function update(Task $task, UpdateTaskDto $params): Task
    {
        $task->update([
            'company_id' => $params->companyId,
            'task_type_id' => $params->taskTypeId,
            'technique_id' => $params->techniqueId,
            'address' => $params->address,
            'mileage_value' => $params->mileageValue,
            'mileage_type' => $params->mileageType->value,
            'description' => $params->description,
        ]);

        return $task->load(['documents', 'photos', 'company', 'taskType', 'technique', 'taskStatus']);
    }

    /**
     * @param string $id
     * @return Task
     */
    public function getById(string $id): Task
    {
        return Task::with(['documents', 'photos', 'company', 'taskType', 'technique', 'taskStatus'])->findOrFail($id);
    }
}