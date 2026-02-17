<?php

namespace Modules\Task\Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Company\Models\Company;
use Modules\Task\Models\Task;
use Modules\User\Models\User;
use Tests\TestCase;

/**
 * Документы для задач.
 * Загрузка.
 * Тесты.
 */
class UploadTaskDocumentControllerTest extends TestCase
{
    use WithFaker;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    /**
     * Проверяет, что суперадмин может загружать документы для любой задачи
     */
    public function test_super_admin_can_upload_documents(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->actingAs($superAdmin);

        $company = Company::factory()->create();
        $task = Task::factory()->create(['company_id' => $company->id]);

        $files = [
            UploadedFile::fake()->create('file1.pdf', 100),
            UploadedFile::fake()->create('file2.docx', 50),
        ];

        $response = $this->postJson("/api/v1/tasks/{$task->id}/documents", [
            'documents' => $files,
        ]);

        $response->assertCreated();

        foreach ($files as $file) {
            $this->assertDatabaseHas('task_documents', [
                'task_id' => $task->id,
                'name' => $file->getClientOriginalName(),
            ]);
        }
    }

    /**
     * Проверяет, что админ может загружать документы только для своей компании
     */
    public function test_admin_can_upload_documents_for_own_company(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->admin($company->id)->create();
        $this->actingAs($admin);

        $task = Task::factory()->create(['company_id' => $company->id]);

        $files = [
            UploadedFile::fake()->create('doc.pdf', 80),
        ];

        $response = $this->postJson("/api/v1/tasks/{$task->id}/documents", [
            'documents' => $files,
        ]);


        $response->assertCreated();

        $this->assertDatabaseHas('task_documents', [
            'task_id' => $task->id,
            'name' => 'doc.pdf',
        ]);
    }

    /**
     * Проверяет, что админ не может загружать документы для чужой компании
     */
    public function test_admin_cannot_upload_documents_for_other_company(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $admin = User::factory()->admin($company1->id)->create();
        $this->actingAs($admin);

        $task = Task::factory()->create(['company_id' => $company2->id]);

        $files = [
            UploadedFile::fake()->create('file.pdf', 50),
        ];

        $response = $this->postJson("/api/v1/tasks/{$task->id}/documents", [
            'documents' => $files,
        ]);

        $response->assertForbidden();
    }

    /**
     * Проверяет, что обычный пользователь не может загружать документы в чужие таски
     */
    public function test_user_cannot_upload_documents_to_other_tasks(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->user($company->id)->create();
        $this->actingAs($user);

        $task = Task::factory()->create(['company_id' => $company->id]);

        $files = [
            UploadedFile::fake()->create('file.pdf', 50),
        ];

        $response = $this->postJson("/api/v1/tasks/{$task->id}/documents", [
            'documents' => $files,
        ]);

        $response->assertForbidden();
    }

    /**
     * Проверяет, что обычный пользователь может загружать доки в свои таски
     */
    public function test_user_can_upload_documents_to_own_tasks(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->user($company->id)->create();
        $this->actingAs($user);

        $task = Task::factory()->create(['company_id' => $company->id, 'author_id' => $user->id]);

        $files = [
            UploadedFile::fake()->create('file.pdf', 50),
        ];

        $response = $this->postJson("/api/v1/tasks/{$task->id}/documents", [
            'documents' => $files,
        ]);

        $response->assertSuccessful();
    }

    /**
     * Проверяет, что гость получает 401
     */
    public function test_guest_cannot_upload_documents(): void
    {
        $task = Task::factory()->create();

        $files = [
            UploadedFile::fake()->create('file.pdf', 50),
        ];

        $response = $this->postJson("/api/v1/tasks/{$task->id}/documents", [
            'documents' => $files,
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Проверяет, что при загрузке документов для несуществующей задачи возвращается 404
     */
    public function test_returns_404_if_task_not_found(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->actingAs($superAdmin);

        $uuid = Str::uuid();

        $files = [
            UploadedFile::fake()->create('file.pdf', 50),
        ];

        $response = $this->postJson("/api/v1/tasks/{$uuid}/documents", [
            'documents' => $files,
        ]);

        $response->assertNotFound();
    }
}
