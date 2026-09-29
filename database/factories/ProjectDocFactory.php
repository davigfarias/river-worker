<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectDoc;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectDoc>
 */
class ProjectDocFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(3),
            'category' => fake()->word(),
            'content' => fake()->paragraph(),
            'order' => 0,
        ];
    }
}
