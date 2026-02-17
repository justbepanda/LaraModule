<?php

namespace Modules\Task\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Task\Database\Factories\TaskDocumentFactory;

/**
 * Документы для задач.
 */
class TaskDocument extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'task_documents';
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
    protected static function newFactory(): TaskDocumentFactory
    {
        return TaskDocumentFactory::new();
    }
}
