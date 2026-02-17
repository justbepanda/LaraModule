<?php

namespace Modules\Task\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Статусы задач.
 * Коллекция ресурсов.
 */
class TaskStatusCollection extends ResourceCollection
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'items' => TaskStatusResource::collection($this->collection)
        ];
    }

}