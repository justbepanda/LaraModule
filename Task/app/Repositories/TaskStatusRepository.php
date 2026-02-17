<?php

namespace Modules\Task\Repositories;

use Modules\Task\Dto\SyncTaskStatusDto;
use Modules\Task\Models\TaskStatus;
use Illuminate\Support\Collection;

/**
 * Статусы задач.
 * Репозиторий.
 */
class TaskStatusRepository
{
    /**
     * Синхронизация статусов задач.
     *
     * @param Collection $statuses
     * @return void
     */
    public function sync(Collection $statuses): void
    {
        $externalUuids = $statuses->pluck('externalUuid');

        $data = $statuses->map(
            fn (SyncTaskStatusDto $dto) => [
                'external_uuid' => $dto->externalUuid,
                'name'        => $dto->name,
                'updated_at'  => now(),
            ]
        )->all();

        // Восстановить удаленные записи, если они снова пришли
        TaskStatus::withTrashed()
            ->whereIn('external_uuid', $externalUuids)
            ->restore();

        // Обновить или создать
        TaskStatus::upsert(
            $data,
            ['external_uuid'],
            ['name', 'updated_at']
        );

        // Мягко удалить отсутствующие (soft delete)
        TaskStatus::whereNotIn('external_uuid', $externalUuids)->delete();
    }

    /**
     * @return Collection
     */
    public function getAll(): Collection
    {
        return TaskStatus::query()->orderBy('name')->get();
    }
}
