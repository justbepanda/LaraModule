<?php

namespace Modules\Task\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Task\Models\TaskPhoto;

/**
 * Фабрика фото задачи.
 */
class TaskPhotoFactory extends Factory
{
    protected $model = TaskPhoto::class;

    /**
     * Определение модели.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'task_id' => $this->faker->uuid(),
            'path' => 'photos/' . $this->faker->uuid . '.jpg',
            'name' => $this->faker->words(2, true) . '.jpg',
        ];
    }
}
