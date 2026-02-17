<?php

namespace Modules\Task\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Task\Database\Factories\TaskPhotoFactory;

/**
 * Фотографии для задач.
 */
class TaskPhoto extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'task_photos';

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'task_id',
        'path',
        'name',
    ];

    /**
     * Create a new factory instance for the model.
     *
     */
    protected static function newFactory(): TaskPhotoFactory
    {
        return TaskPhotoFactory::new();
    }
}
