<?php

namespace Modules\Task\Transformers;

use App\Http\Resources\Resource;
use Illuminate\Http\Request;

/**
 * Статусы задач.
 * Ресурс.
 */
class TaskStatusResource extends Resource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
        ];
    }
}