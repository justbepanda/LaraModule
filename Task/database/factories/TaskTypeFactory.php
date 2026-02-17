<?php

namespace Modules\Task\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Task\Models\TaskType;

/**
 * Фабрика типов задач.
 */
class TaskTypeFactory extends Factory
{
    protected $model = TaskType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Ремонт',
                'ТО',
                'Покупка запчастей',
                'Покупка расходников',
            ]),
        ];
    }
}
