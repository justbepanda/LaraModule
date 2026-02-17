<?php

namespace Modules\Task\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Task\Models\TaskType;

class TaskTypeRepository
{
    /**
     * @return Collection
     */
    public function getAll(): Collection
    {
      return TaskType::query()->orderBy('name')->get();
    }
}