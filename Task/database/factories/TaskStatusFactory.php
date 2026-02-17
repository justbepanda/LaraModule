<?php

namespace Modules\Task\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Task\Models\TaskStatus;

/**
 * Статусы задач.
 * Фабрика.
 */
class TaskStatusFactory extends Factory
{
    protected $model = TaskStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'external_uuid' => $this->faker->unique()->numberBetween(1, 999999),
            'name' => $this->faker->words(2, true),
        ];
    }
}
