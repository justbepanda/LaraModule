<?php

namespace Modules\Task\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Company\Models\Company;
use Modules\Task\Database\Factories\TaskFactory;
use Modules\Technique\Enums\TechniqueMileageType;
use Modules\Technique\Models\Technique;
use Modules\User\Models\User;

/**
 * Задачи.
 */
class Task extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'tasks';
    public $timestamps = true;
    protected $keyType = 'string';

    public $incrementing = false;
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'task_type_id',
        'technique_id',
        'address',
        'mileage_value',
        'mileage_type',
        'description',
        'author_id',
    ];

    /**
     * @return Attribute
     */
    protected function mileageType(): Attribute
    {
        return Attribute::make(
            get: static fn(?string $value): ?TechniqueMileageType => $value ? TechniqueMileageType::from($value) : null,
        );
    }

    /**
     * @return HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(TaskDocument::class, 'task_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function photos(): HasMany
    {
        return $this->hasMany(TaskPhoto::class, 'task_id', 'id');
    }


    /**
     * Create a new factory instance for the model.
     *
     */
    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo
     */
    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class, 'task_type_id');
    }

    /**
     * @return BelongsTo
     */
    public function technique(): BelongsTo
    {
        return $this->belongsTo(Technique::class, 'technique_id');
    }

    /**
     * @return BelongsTo
     */
    public function taskStatus(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
