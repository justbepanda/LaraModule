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

/**
 * Задачи.
 * Просмотр.
 * Тесты.
 */
class GetTaskControllerTest extends TestCase
{
    use WithFaker;

    /**
     * Проверяет, что супер администратор может просматривать любую задачу.
     */
    public function test_super_admin_can_view_any_task(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->actingAs($superAdmin);

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $taskType = TaskType::factory()->create();
        $techA = Technique::factory()->create(['company_id' => $companyA->id]);
        $techB = Technique::factory()->create(['company_id' => $companyB->id,]);

        $taskA = Task::factory()->create([
            'company_id' => $companyA->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $techA->id,
            'author_id' => $superAdmin->id,
        ]);

        $taskB = Task::factory()->create([
            'company_id' => $companyB->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $techB->id,
            'author_id' => $superAdmin->id,
        ]);

        $responseA = $this->getJson("/api/v1/tasks/{$taskA->id}");
        $responseB = $this->getJson("/api/v1/tasks/{$taskB->id}");

        $responseA->assertSuccessful();
        $responseB->assertSuccessful();

        $responseA->assertJson(fn (AssertableJson $json) =>
        $json->where('message', 'Успешно')
            ->whereType('request_id', 'string')
            ->has('data', fn (AssertableJson $data) =>
            $data
                ->where('id', $taskA->id)
                ->where('company.id', $companyA->id)
                ->whereType('task_type.id', 'string')
                ->whereType('technique.id', 'string')
                ->whereType('address', 'string')
                ->whereType('mileage.value', 'string')
                ->whereType('mileage.type', 'string')
                ->whereType('description', 'string')
                ->whereType('created_at', 'string')
                ->whereType('updated_at', 'string')
                ->where('author_id', $superAdmin->id)
                ->etc()
            )
            ->etc()
        );
    }

    /**
     * Проверяет, что администратор может просматривать задачи только своей компании.
     */
    public function test_admin_can_view_task_only_from_own_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $adminA = User::factory()->admin($companyA->id)->create();
        $this->actingAs($adminA);

        $taskType = TaskType::factory()->create();
        $techA = Technique::factory()->create(['company_id' => $companyA->id]);
        $techB = Technique::factory()->create(['company_id' => $companyB->id]);

        $taskA = Task::factory()->create([
            'company_id' => $companyA->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $techA->id,
        ]);

        $taskB = Task::factory()->create([
            'company_id' => $companyB->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $techB->id,
        ]);

        // Свою задачу видит
        $this->getJson("/api/v1/tasks/{$taskA->id}")
            ->assertSuccessful();

        // Чужую — нет
        $this->getJson("/api/v1/tasks/{$taskB->id}")
            ->assertForbidden();
    }

    /**
     * Проверяет, что обычный пользователь может просматривать только задачи своей компании.
     */
    public function test_user_can_view_only_own_company_tasks(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $userA = User::factory()->user($companyA->id)->create();
        $this->actingAs($userA);

        $taskType = TaskType::factory()->create();
        $techA = Technique::factory()->create(['company_id' => $companyA->id]);
        $techB = Technique::factory()->create(['company_id' => $companyB->id]);

        $taskA = Task::factory()->create([
            'company_id' => $companyA->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $techA->id,
        ]);

        $taskB = Task::factory()->create([
            'company_id' => $companyB->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $techB->id,
        ]);

        $this->getJson("/api/v1/tasks/{$taskA->id}")
            ->assertSuccessful();

        $this->getJson("/api/v1/tasks/{$taskB->id}")
            ->assertForbidden();
    }

    /**
     * Проверяет, что неавторизованный запрос возвращает 401.
     */
    public function test_guest_cannot_access(): void
    {
        $task = Task::factory()->create();

        $this->getJson("/api/v1/tasks/{$task->id}")
            ->assertUnauthorized();
    }

    /**
     * Проверяет, что при запросе несуществующей задачи возвращается 404.
     */
    public function test_returns_404_if_task_not_found(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->actingAs($superAdmin);

        $uuid = Str::uuid();

        $this->getJson("/api/v1/tasks/{$uuid}")
            ->assertNotFound();
    }
}
