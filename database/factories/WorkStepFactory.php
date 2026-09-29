<?php

namespace Database\Factories;

use App\Models\WorkItem;
use App\Models\WorkStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkStep>
 */
class WorkStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_item_id' => WorkItem::factory(),
            'title' => fake()->sentence(3),
            'is_completed' => false,
            'order' => 0,
        ];
    }
}
