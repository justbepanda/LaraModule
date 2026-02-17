<?php

namespace Modules\Task\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Task\Repositories\TaskTypeRepository;

class TaskTypeService
{
    /**
     * @param TaskTypeRepository $taskTypeRepository
     */
    public function __construct(
        private readonly TaskTypeRepository $taskTypeRepository,
    )
    {}

    /**
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->taskTypeRepository->getAll();
    }

}