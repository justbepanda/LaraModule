<?php

namespace Modules\Task\Tests\Unit\Repositories;

use Modules\Task\Dto\SyncTaskStatusDto;
use Modules\Task\Models\TaskStatus;
use Modules\Task\Repositories\TaskStatusRepository;
use Tests\TestCase;

/**
 * Статусы задач.
 * Репозиторий.
 * Тесты.
 */
class TaskStatusRepositoryTest extends TestCase
{
    /**
     * Новые статусы создаются.
     *
     * @return void
     */
    public function test_creates_new_statuses(): void
    {
        $taskStatusRepository = new TaskStatusRepository();

        $statuses = collect([
            new SyncTaskStatusDto(externalUuid: 10, name: 'Новый'),
            new SyncTaskStatusDto(externalUuid: 20, name: 'В работе'),
        ]);

        $taskStatusRepository->sync($statuses);

        $this->assertDatabaseHas('task_statuses', [
            'external_uuid' => 10,
            'name'        => 'Новый',
            'deleted_at'  => null,
        ]);

        $this->assertDatabaseHas('task_statuses', [
            'external_uuid' => 20,
            'name'        => 'В работе',
            'deleted_at'  => null,
        ]);
    }

    /**
     * Существующие статусы обновляются.
     *
     * @return void
     */
    public function test_updates_existing_statuses(): void
    {
        TaskStatus::factory()->create([
            'external_uuid' => 10,
            'name'        => 'Старое имя',
        ]);

        $taskStatusRepository = new TaskStatusRepository();

        $statuses = collect([
            new SyncTaskStatusDto(externalUuid: 10, name: 'Новое имя'),
        ]);

        $taskStatusRepository->sync($statuses);

        $this->assertDatabaseHas('task_statuses', [
            'external_uuid' => 10,
            'name'        => 'Новое имя',
            'deleted_at'  => null,
        ]);
    }

    /**
     * Ранее удалённые записи восстанавливаются.
     *
     * @return void
     */
    public function test_restores_soft_deleted_statuses(): void
    {
        $status = TaskStatus::factory()->create([
            'external_uuid' => 10,
            'name'        => 'Test',
        ]);

        $status->delete();

        $this->assertSoftDeleted('task_statuses', ['external_uuid' => 10]);

        $taskStatusRepository = new TaskStatusRepository();

        $statuses = collect([
            new SyncTaskStatusDto(externalUuid: 10, name: 'Test'),
        ]);

        $taskStatusRepository->sync($statuses);

        $this->assertDatabaseHas('task_statuses', [
            'external_uuid' => 10,
            'deleted_at'  => null,
        ]);
    }

    /**
     * Отсутствующие статусы мягко удаляются.
     *
     * @return void
     */
    public function test_soft_deletes_missing_statuses(): void
    {
        TaskStatus::factory()->create([
            'external_uuid' => 10,
            'name'        => 'Test1',
        ]);

        TaskStatus::factory()->create([
            'external_uuid' => 20,
            'name'        => 'Test2',
        ]);

        $taskStatusRepository = new TaskStatusRepository();

        $statuses = collect([
            new SyncTaskStatusDto(externalUuid: 10, name: 'Test1'),
        ]);

        $taskStatusRepository->sync($statuses);

        $this->assertSoftDeleted('task_statuses', [
            'external_uuid' => 20,
        ]);
    }
}
