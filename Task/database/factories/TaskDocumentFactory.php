<?php

namespace Modules\Task\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Task\Models\TaskDocument;

/**
 * Документы задачи.
 * Фабрика.
 */
class TaskDocumentFactory extends Factory
{
    protected $model = TaskDocument::class;


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'task_id' => $this->faker->uuid(),
            'path' => 'documents/' . $this->faker->uuid . '.pdf',
            'name' => $this->faker->words(3, true) . '.pdf',
        ];
    }
}