<?php

namespace Database\Factories;

use App\Models\WorkFile;
use App\Models\WorkItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkFile>
 */
class WorkFileFactory extends Factory
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
            'file_path' => 'app/Services/'.fake()->word().'.php',
            'reason_notes' => fake()->sentence(),
            'is_reviewed' => false,
        ];
    }
}
