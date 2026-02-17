<?php

namespace Modules\Task\Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Modules\Company\Models\Company;
use Modules\Task\Models\TaskType;
use Modules\Technique\Enums\TechniqueMileageType;
use Modules\Technique\Models\Technique;
use Modules\User\Enums\UserRoleType;
use Modules\User\Models\User;
use Tests\TestCase;

/**
 * Пользователи.
 * Создание.
 * Тесты.
 */
class CreateTaskControllerTest extends TestCase
{
    use WithFaker;

    /**
     * Успешное создание задачи суперадмином.
     */
    public function test_creates_task_successfully(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->actingAs($superAdmin);

        $company = Company::factory()->create();
        $technique = Technique::factory()->create([
            'company_id' => $company->id,
        ]);
        $taskType = TaskType::factory()->create();

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

        $response = $this->postJson('/api/v1/tasks', $payload);

        $response->assertCreated();

        $response->assertJsonStructure([
            'message',
            'request_id',
            'data' => [
                'id',
                'company',
                'task_type',
                'technique',
                'address',
                'mileage',
                'description',
                'created_at',
                'updated_at',
                'documents',
                'photos',
            ],
        ]);

        $this->assertDatabaseHas('tasks', [
            'company_id' => $company->id,
            'task_type_id' => $payload['task_type_id'],
            'technique_id' => $payload['technique_id'],
            'address' => $payload['address'],
            'mileage_value' => $payload['mileage']['value'],
            'mileage_type' => $payload['mileage']['type'],
            'description' => $payload['description'],
            'author_id' => $superAdmin->id,
        ]);
    }

    /**
     * Админ может создать задачу в своей компании.
     */
    public function test_admin_can_create_task_in_own_company(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->admin($company->id)->create();
        $technique = Technique::factory()->create([
            'company_id' => $company->id,
        ]);
        $taskType = TaskType::factory()->create();

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
            'description' => $this->faker->sentence,
        ];

        $response = $this->postJson('/api/v1/tasks', $payload);
        $response->assertSuccessful();
    }

    /**
     * Админ не может создавать задачи в другой компании.
     */
    public function test_admin_cannot_create_task_in_another_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $admin = User::factory()->admin($companyA->id)->create();
        $technique = Technique::factory()->create([
            'company_id' => $companyA->id,
        ]);
        $taskType = TaskType::factory()->create();

        $this->actingAs($admin);

        $payload = [
            'company_id' => $companyB->id,
            'task_type_id' => $taskType->id,
            'technique_id' => $technique->id,
            'address' => $this->faker->address,
            'mileage' => [
                'value' => $this->faker->numberBetween(0, 5000),
                'type' => $this->faker->randomElement(TechniqueMileageType::cases())->value,
            ],
            'description' => $this->faker->sentence,
        ];

        $response = $this->postJson('/api/v1/tasks', $payload);
        $response->assertForbidden();
    }

    /**
     * Обычный пользователь может создавать задачи в своей компании.
     */
    public function test_user_can_create_task_in_own_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->user($company->id)->create();
        $technique = Technique::factory()->create([
            'company_id' => $company->id,
        ]);
        $taskType = TaskType::factory()->create();

        $this->actingAs($user);

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

        $response = $this->postJson('/api/v1/tasks', $payload);
        $response->assertSuccessful();
    }
}
