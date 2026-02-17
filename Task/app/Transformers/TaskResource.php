<?php

namespace Modules\Task\Transformers;

use App\Http\Resources\Resource;
use App\Transformers\ResourceHasPaginate;
use Illuminate\Http\Request;
use Modules\Company\Transformers\CompanyResource;
use Modules\Task\Models\Task;
use Modules\Technique\Transformers\TechniqueResource;

/**
 * @property Task resource
 */
class TaskResource extends Resource
{
    use ResourceHasPaginate;

    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'company' => $this->whenLoaded('company', function () {
                return new CompanyResource($this->resource->company);
            }),
            'task_type' => $this->whenLoaded('taskType', function () {
                return new TaskTypeResource($this->resource->taskType);
            }),
            'task_status' => $this->whenLoaded('taskStatus', function () {
                return new TaskStatusResource($this->resource->taskStatus);
            }),
            'technique' => $this->whenLoaded('technique', function () {
                return new TechniqueResource($this->resource->technique);
            }),
            'address' => $this->resource->address,
            'mileage' => [
                'value' => $this->resource->mileage_value,
                'type' => $this->resource->mileage_type,
            ],
            'description' => $this->resource->description,
            'documents' => $this->resource->documents->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'url' => $doc->path ? asset("storage/{$doc->path}") : null,
                ];
            }),
            'photos' => $this->resource->photos->map(function($photo) {
                return [
                    'id' => $photo->id,
                    'name' => $photo->name,
                    'url' => $photo->path ? asset("storage/{$photo->path}") : null,
                ];
            }),
            'author_id' => $this->resource->author_id,
            'created_at' => $this->resource->created_at?->toDateTimeString(),
            'updated_at' => $this->resource->updated_at?->toDateTimeString(),
        ];
    }
}