<?php

namespace Modules\Task\Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Modules\Task\Models\TaskStatus;

use Modules\User\Models\User;
use Tests\TestCase;

/**
 * Статусы задач.
 * Просмотр списка.
 * Тесты.
 */
class GetTaskStatusesControllerTest extends TestCase
{
    use WithFaker;

    /**
     * Проверяет, что список статусов задач успешно получен.
     */
    public function test_get_all_task_statuses(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        TaskStatus::factory()->create();

        $response = $this->getJson('/api/v1/task-statuses');

        $response->assertSuccessful();

        $response->assertJson(fn (AssertableJson $json) =>
        $json->where('message', 'Успешно')
            ->hasAll(['request_id', 'data'])
            ->has('data.items')
            ->has('data.items.0', fn ($task) =>
            $task->whereAllType([
                'id' => 'string',
                'name' => 'string',
            ])
            )
        );
    }
}
