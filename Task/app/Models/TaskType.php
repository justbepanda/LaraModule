<?php

namespace Modules\Task\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Task\Database\Factories\TaskTypeFactory;

class TaskType extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'task_types';
    public $timestamps = false;
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name'];

    /**
     * Create a new factory instance for the model.
     *
     */
    protected static function newFactory(): TaskTypeFactory
    {
        return TaskTypeFactory::new();
    }
}
