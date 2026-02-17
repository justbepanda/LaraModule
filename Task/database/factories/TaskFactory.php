<?php

namespace Modules\Task\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Company\Models\Company;
use Modules\Task\Models\Task;
use Modules\Task\Models\TaskType;
use Modules\Technique\Enums\TechniqueMileageType;
use Modules\Technique\Models\Technique;

/**
 * Задачи.
 * Фабрика.
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Configure the factory.
     *
     * @return static
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Task $task) {
            $task->company_id ??= Company::factory()->create()->id;
            $task->technique_id ??= Technique::factory()
                ->create(['company_id' => $task->company_id])
                ->id;
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'company_id' => null,
            'task_type_id' => TaskType::factory(),
            'technique_id' => null,
            'address' => $this->faker->address,
            'mileage_value' => $this->faker->numberBetween(0, 5000),
            'mileage_type' => $this->faker->randomElement(TechniqueMileageType::cases())->value,
            'description' => $this->faker->sentence,
            'author_id' => null
        ];
    }
}
