<?php

namespace Modules\Task\Transformers;

use App\Http\Resources\Resource;
use App\Transformers\ResourceHasPaginate;
use Illuminate\Http\Request;
use Modules\Task\Models\TaskDocument;

/**
 * Фотографии для задач.
 * Ресурс.
 *
 * @property TaskDocument resource
 */
class TaskPhotoResource extends Resource
{
    use ResourceHasPaginate;

    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'name' => $this->name,
            'url' => $this->path ? asset("storage/{$this->path}") : null,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}