<?php

namespace Modules\Task\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Task\Database\Factories\TaskStatusFactory;

/**
 * Статусы задач.
 */
class TaskStatus extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'task_statuses';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'external_uuid',
        'name',
    ];

    /**
     * Create a new factory instance for the model.
     *
     */
    protected static function newFactory(): TaskStatusFactory
    {
        return TaskStatusFactory::new();
    }
}
