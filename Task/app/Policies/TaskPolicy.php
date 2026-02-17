<?php

namespace Modules\Task\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Company\Models\Company;
use Modules\Task\Models\Task;
use Modules\User\Models\User;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Company|null $company
     * @return bool
     */
    public function viewAny(User $user, ?Company $company = null): bool
    {
        return $user->isSuperadministrator() ||
            ($company && $user->company_id === $company->id);
    }

    /**
     * @param User $user
     * @param Task $task
     * @return bool
     */
    public function view(User $user, Task $task): bool
    {
        return $user->isSuperadministrator() ||
            ($user->company_id === $task->company_id);
    }

    /**
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function create(User $user, Company $company): bool
    {
        return $user->isSuperadministrator() ||
            ($user->company_id === $company->id);
    }

    /**
     * @param User $user
     * @param Task $task
     * @return bool
     */
    public function update(User $user, Task $task): bool
    {
        return $user->isSuperadministrator() ||
            ($user->isAdministrator() && $user->company_id === $task->company_id) ||
            ($task->author_id === $user->id);
    }

    /**
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function delete(User $user, Company $company): bool
    {
        return false;
    }

    /**
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function restore(User $user, Company $company): bool
    {
        return false;
    }

    /**
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function forceDelete(User $user, Company $company): bool
    {
        return false;
    }
}
