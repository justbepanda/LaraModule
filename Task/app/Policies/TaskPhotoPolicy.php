<?php

namespace Modules\Task\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Task\Models\Task;
use Modules\User\Models\User;

/**
 * Фотографии для задач.
 * Политика.
 */
class TaskPhotoPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Task $task
     * @return bool
     */
    public function create(User $user, Task $task): bool
    {
        return $user->isSuperadministrator() ||
            ($user->isAdministrator() && $user->company_id === $task->company_id) ||
            $task->author_id === $user->id;
    }
}
