<?php

namespace Modules\Task\Tests\Unit\Services;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Company\Models\Company;
use Modules\Task\DTO\CreateTaskDto;
use Modules\Task\DTO\UpdateTaskDto;
use Modules\Task\Models\Task;
use Modules\Task\Models\TaskType;
use Modules\Task\Services\TaskService;
use Modules\Technique\Enums\TechniqueMileageType;
use Modules\Technique\Models\Technique;
use Modules\Technique\Models\TechniqueHistory;
use Modules\User\Models\User;
use Tests\TestCase;

/**
 * Задачи.
 * Сервис.
 * Тесты.
 */
class TaskServiceTest extends TestCase
{
    /**
     * Проверка обновления пробега техники при создании задачи.
     */
    public function test_updates_technique_mileage_on_task_create(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $technique = Technique::factory()->create([
            'mileage_value' => fake()->numberBetween(1, 100),
            'mileage_type' => TechniqueMileageType::HOURS->value,
        ]);

        $taskType = TaskType::factory()->create();
        $company = Company::factory()->create();

        $dto = new CreateTaskDto(
            companyId: $company->id,
            taskTypeId: $taskType->id,
            techniqueId: $technique->id,
            address: fake()->address(),
            mileageValue: fake()->numberBetween(101, 1000),
            mileageType: TechniqueMileageType::KILOMETERS,
            description: fake()->sentence(),
            authorId: $user->id,
        );

        $taskService = app(TaskService::class);
        $taskService->create($dto);

        $technique->refresh();

        $this->assertEquals($dto->mileageValue, $technique->mileage_value);
        $this->assertEquals(TechniqueMileageType::KILOMETERS, $technique->mileage_type);

        $this->assertDatabaseHas(TechniqueHistory::class, [
            'technique_id' => $technique->id,
            'mileage_value' => $dto->mileageValue,
            'mileage_type' => TechniqueMileageType::KILOMETERS->value,
        ]);
    }

    /**
     * Проверка обновления пробега техники при обновлении задачи.
     */
    public function test_updates_technique_mileage_on_task_update(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $technique = Technique::factory()->create([
            'mileage_value' => fake()->numberBetween(1, 100),
            'mileage_type' => TechniqueMileageType::HOURS->value,
        ]);

        $task = Task::factory()->create([
            'technique_id' => $technique->id,
            'company_id' => $technique->company_id,
        ]);

        $dto = new UpdateTaskDto(
            companyId: $task->company_id,
            taskTypeId: $task->task_type_id,
            techniqueId: $technique->id,
            address: $task->address,
            mileageValue: fake()->numberBetween(101, 1000),
            mileageType: TechniqueMileageType::KILOMETERS,
            description: $task->description,
        );

        $taskService = app(TaskService::class);
        $taskService->update($task, $dto);

        $technique->refresh();

        $this->assertEquals($dto->mileageValue, $technique->mileage_value);
        $this->assertEquals(TechniqueMileageType::KILOMETERS, $technique->mileage_type);

        $this->assertDatabaseHas(TechniqueHistory::class, [
            'technique_id' => $technique->id,
            'mileage_value' => $dto->mileageValue,
            'mileage_type' => TechniqueMileageType::KILOMETERS,
        ]);
    }
}
