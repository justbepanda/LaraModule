<?php

namespace Modules\Task\Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Modules\Company\Models\Company;
use Modules\Task\Models\Task;
use Modules\Task\Models\TaskType;
use Modules\Technique\Models\Technique;

use Modules\User\Models\User;
use Tests\TestCase;

/**
 * Задачи.
 * Просмотр списка.
 * Тесты.
 */
class GetTasksControllerTest extends TestCase
{
    use WithFaker;

    /**
     * Проверяет, что супер администратор видит все задачи.
     */
    public function test_super_admin_can_view_all_tasks(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->actingAs($superAdmin);

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $taskType = TaskType::factory()->create();
        $techniqueA = Technique::factory()->create(['company_id' => $companyA->id]);
        $techniqueB = Technique::factory()->create(['company_id' => $companyB->id]);

        Task::factory()->count(2)->create([
            'company_id' => $companyA->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $techniqueA->id,
        ]);

        Task::factory()->count(2)->create([
            'company_id' => $companyB->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $techniqueB->id,
        ]);

        $response = $this->getJson('/api/v1/tasks');

        $response->assertSuccessful();

        $response->assertJson(fn(AssertableJson $json) => $json->where('message', 'Успешно')
            ->hasAll(['request_id', 'data'])
            ->has('data.items')
            ->has('data.items.0', fn(AssertableJson $task) => $task
                ->whereType('id', 'string')
                ->whereType('company.id', 'string')
                ->whereType('task_type.id', 'string')
                ->whereType('technique.id', 'string')
                ->whereType('address', 'string')
                ->whereType('mileage.value', 'string')
                ->whereType('mileage.type', 'string')
                ->whereType('description', 'string')
                ->whereType('created_at', 'string')
                ->whereType('updated_at', 'string')
                ->etc()
            )
        );
    }

    /**
     * Проверяет, что администратор видит задачи только своей компании.
     */
    public function test_admin_can_view_only_own_company_tasks(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $adminA = User::factory()->admin($companyA->id)->create();
        $this->actingAs($adminA);

        $taskType = TaskType::factory()->create();
        $techniqueA = Technique::factory()->create(['company_id' => $companyA->id]);
        $techniqueB = Technique::factory()->create(['company_id' => $companyB->id]);

        $ownTask = Task::factory()->create([
            'company_id' => $companyA->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $techniqueA->id,
        ]);

        $foreignTask = Task::factory()->create([
            'company_id' => $companyB->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $techniqueB->id,
        ]);

        $response = $this->getJson('/api/v1/tasks');

        $response->assertSuccessful();

        $response->assertJson(fn(AssertableJson $json) => $json->where('message', 'Успешно')
            ->whereType('request_id', 'string')
            ->has('data.items', 1)
            ->has('data.items.0', fn(AssertableJson $task) => $task
                ->where('id', $ownTask->id)
                ->where('company.id', $companyA->id)
                ->etc()
            )
            ->etc()
        );

        $this->assertStringNotContainsString($foreignTask->id, $response->getContent());
    }

    /**
     * Проверяет, что обычный пользователь видит задачи своей компании.
     */
    public function test_user_can_view_tasks_of_own_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $userA = User::factory()->user($companyA->id)->create();
        $this->actingAs($userA);

        $taskType = TaskType::factory()->create();
        $techniqueA = Technique::factory()->create(['company_id' => $companyA->id]);
        $techniqueB = Technique::factory()->create(['company_id' => $companyB->id]);

        $ownTask = Task::factory()->create([
            'company_id' => $companyA->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $techniqueA->id,
        ]);

        $foreignTask = Task::factory()->create([
            'company_id' => $companyB->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $techniqueB->id,
        ]);

        $response = $this->getJson('/api/v1/tasks');

        $response->assertSuccessful();

        $response->assertJson(fn(AssertableJson $json) => $json->where('message', 'Успешно')
            ->whereType('request_id', 'string')
            ->has('data.items', 1)
            ->has('data.items.0', fn(AssertableJson $task) => $task
                ->where('id', $ownTask->id)
                ->where('company.id', $companyA->id)
                ->etc()
            )
            ->etc()
        );

        $this->assertStringNotContainsString($foreignTask->id, $response->getContent());
    }

    /**
     * Проверяет, что неавторизованный пользователь не может получить задачи.
     */
    public function test_guest_cannot_access(): void
    {
        $this->getJson('/api/v1/tasks')->assertUnauthorized();
    }
}
