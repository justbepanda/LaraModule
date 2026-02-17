<?php

namespace Modules\Task\Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Modules\Company\Models\Company;
use Modules\Task\Models\Task;
use Modules\Task\Models\TaskType;
use Modules\Technique\Models\Technique;
use Modules\User\Models\User;
use Tests\TestCase;
use Modules\Technique\Enums\TechniqueMileageType;

/**
 * Задачи.
 * Обновление.
 * Тесты.
 */
class UpdateTaskControllerTest extends TestCase
{
    use WithFaker;

    /**
     * Проверяет, что суперадмин может обновлять любую задачу
     */
    public function test_super_admin_can_update_any_task(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->actingAs($superAdmin);

        $company = Company::factory()->create();
        $taskType = TaskType::factory()->create();
        $technique = Technique::factory()->create(['company_id' => $company->id]);
        $task = Task::factory()->create(['company_id' => $company->id]);

        $payload = [
            'company_id' => $company->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $technique->id,
            'address' => $this->faker->address,
            'mileage' => [
                'value' => $this->faker->numberBetween(0, 5000),
                'type' => $this->faker->randomElement(TechniqueMileageType::cases())->value,
            ],
            'description' => $this->faker->sentence,
        ];

        $response = $this->putJson("/api/v1/tasks/{$task->id}", $payload);

        $response->assertSuccessful();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'company_id' => $company->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $technique->id,
            'address' => $payload['address'],
            'description' => $payload['description'],
        ]);
    }

    /**
     * Проверяет, что админ может обновлять задачи своей компании
     */
    public function test_admin_can_update_task_in_own_company(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->admin($company->id)->create();

        $taskType = TaskType::factory()->create();
        $technique = Technique::factory()->create(['company_id' => $company->id]);
        $task = Task::factory()->create(['company_id' => $company->id]);

        $this->actingAs($admin);

        $payload = [
            'company_id' => $company->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $technique->id,
            'address' => $this->faker->address,
            'mileage' => [
                'value' => $this->faker->numberBetween(0, 5000),
                'type' => $this->faker->randomElement(TechniqueMileageType::cases())->value,
            ],
            'description' => 'Обновочка',
        ];

        $response = $this->putJson("/api/v1/tasks/{$task->id}", $payload);
        $response->assertSuccessful();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'company_id' => $company->id,
            'description' => 'Обновочка',
        ]);
    }

    /**
     * Проверяет, что обычный пользователь не может чужие задачи
     */
    public function test_user_can_update_only_own_task(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->user($company->id)->create();
        $task = Task::factory()->create(['company_id' => $company->id]);

        $ownTask = Task::factory()->create([
            'company_id' => $company->id,
            'author_id' => $user->id
        ]);

        $taskType = TaskType::factory()->create();
        $technique = Technique::factory()->create(['company_id' => $company->id]);

        $payload = [
            'company_id' => $company->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $technique->id,
            'address' => $this->faker->address,
            'mileage' => [
                'value' => $this->faker->numberBetween(0, 5000),
                'type' => $this->faker->randomElement(TechniqueMileageType::cases())->value,
            ],
            'description' => $this->faker->sentence,
        ];

        $this->actingAs($user);

        $response = $this->putJson("/api/v1/tasks/{$task->id}", $payload);
        $response->assertForbidden();
        $this->putJson("/api/v1/tasks/{$ownTask->id}", $payload)->assertSuccessful();
    }

    /**
     * Проверяет, что неавторизованный пользователь получает 401
     */
    public function test_guest_cannot_update_task(): void
    {
        $task = Task::factory()->create();

        $payload = [
            'address' => 'Unauthorized attempt',
        ];

        $response = $this->putJson("/api/v1/tasks/{$task->id}", $payload);
        $response->assertUnauthorized();
    }

    /**
     * Проверяет, что при обновлении несуществующей задачи возвращается validation error
     */
    public function test_returns_validation_error_if_task_not_found(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->actingAs($superAdmin);

        $uuid = Str::uuid();

        $payload = [
            'address' => 'Invalid',
        ];

        $response = $this->putJson("/api/v1/tasks/{$uuid}", $payload);
        $response->assertJsonMissingValidationErrors();
    }
}
