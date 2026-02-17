<?php

namespace Modules\Task\Tests\Unit\Policies;

use Illuminate\Foundation\Testing\WithFaker;
use Modules\Task\Models\Task;
use Modules\Company\Models\Company;
use Modules\Task\Models\TaskType;
use Modules\Task\Policies\TaskPolicy;
use Modules\Technique\Models\Technique;
use Modules\User\Models\User;
use Tests\TestCase;

/**
 * Задачи
 * Политика.
 * Тесты.
 */
class TaskPolicyTest extends TestCase
{
    use WithFaker;

    private TaskPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TaskPolicy();
    }

    /**
     * Суперадмин может видеть все задачи в любой компании
     */
    public function test_superadmin_can_view_any_tasks(): void
    {
        $superadmin = User::factory()->superAdmin()->create();
        $company = Company::factory()->create();

        $this->assertTrue($this->policy->viewAny($superadmin, $company));
    }

    /**
     * Админ может видеть задачи только своей компании
     */
    public function test_admin_can_view_tasks_of_own_company(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->admin($company->id)->create();

        $this->assertTrue($this->policy->viewAny($admin, $company));
    }

    /**
     * Админ не может видеть задачи другой компании
     */
    public function test_admin_cannot_view_tasks_of_another_company(): void
    {
        $company = Company::factory()->create();
        $anotherCompany = Company::factory()->create();
        $admin = User::factory()->admin($company->id)->create();

        $this->assertFalse($this->policy->viewAny($admin, $anotherCompany));
    }

    /**
     * Обычный пользователь может видеть список задач своей компании
     */
    public function test_user_can_view_tasks_of_own_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->user($company->id)->create();

        $this->assertTrue($this->policy->viewAny($user, $company));
    }

    /**
     * Обычный пользователь не может видеть список задач чужой компании
     */
    public function test_user_cannot_view_tasks_of_another_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->user($company->id)->create();
        $anotherCompany = Company::factory()->create();

        $this->assertFalse($this->policy->viewAny($user, $anotherCompany));
    }

    /**
     * Суперадмин может видеть любую задачу
     */
    public function test_superadmin_can_view_any_task(): void
    {
        $superadmin = User::factory()->superAdmin()->create();
        $targetTask = Task::factory()->create();

        $this->assertTrue($this->policy->view($superadmin, $targetTask));
    }

    /**
     * Админ может видеть задачу своей компании
     */
    public function test_admin_can_view_task_from_own_company(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->admin($company->id)->create();
        $target = Task::factory()->create(['company_id' => $company->id]);

        $this->assertTrue($this->policy->view($admin, $target));
    }

    /**
     * Админ не может видеть задачу другой компании
     */
    public function test_admin_cannot_view_task_from_other_company(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->admin($company->id)->create();
        $target = Task::factory()->create();

        $this->assertFalse($this->policy->view($admin, $target));
    }

    /**
     * Пользователь может видеть задачи своей компании
     */
    public function test_user_can_view_task_from_own_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->user($company->id)->create();
        $target = Task::factory()->create(['company_id' => $company->id]);

        $this->assertTrue($this->policy->view($user, $target));
    }

    /**
     * Пользователь не может видеть задачу другой компании
     */
    public function test_user_cannot_view_task_from_other_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->user($company->id)->create();
        $target = Task::factory()->create();

        $this->assertFalse($this->policy->view($user, $target));
    }

    /**
     * Суперадмин может создавать задачи в любой компании
     */
    public function test_super_admin_can_create_task(): void
    {
        $user = User::factory()->superadmin()->create();
        $company = Company::factory()->create();
        $this->assertTrue($this->policy->create($user, $company));
    }

    /**
     * Админ может создавать задачи в своей компании
     */
    public function test_admin_can_create_task(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->admin($company->id)->create();
        $anotherCompany = Company::factory()->create();

        $this->assertTrue($this->policy->create($user, $company));
        $this->assertFalse($this->policy->create($user, $anotherCompany));
    }

    /**
     * Пользователь может создавать задачи в своей компании
     */
    public function test_user_cant_create_task(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->user($company->id)->create();
        $anotherCompany = Company::factory()->create();

        $this->assertTrue($this->policy->create($user, $company));
        $this->assertFalse($this->policy->create($user, $anotherCompany));
    }

    /**
     * Суперадмин может редактировать задачи в любой компании
     */
    public function test_super_admin_can_edit_task(): void
    {
        $user = User::factory()->superadmin()->create();
        $task = Task::factory()->create();
        $this->assertTrue($this->policy->update($user, $task));
    }

    /**
     * Админ может редактировать задачи в своей компании
     */
    public function test_admin_can_edit_task(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->admin($company->id)->create();
        $task = Task::factory()->create(['company_id' => $company->id]);
        $anotherCompanyTask = Task::factory()->create();

        $this->assertTrue($this->policy->update($user, $task));
        $this->assertFalse($this->policy->update($user, $anotherCompanyTask));
    }

    /**
     * Пользователь не может редактировать задачи
     */
    public function test_user_cant_edit_task(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->user($company->id)->create();
        $task = Task::factory()->create(['company_id' => $company->id]);

        $this->assertFalse($this->policy->update($user, $task));
    }
}
