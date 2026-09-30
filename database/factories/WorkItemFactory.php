<?php

namespace Database\Factories;

use App\Enums\WorkItemKind;
use App\Enums\WorkItemStatus;
use App\Models\Project;
use App\Models\WorkItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkItem>
 */
class WorkItemFactory extends Factory
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
            'title' => fake()->sentence(4),
            'kind' => WorkItemKind::Feat,
            'description' => fake()->paragraph(),
            'status' => WorkItemStatus::Backlog,
        ];
    }
}
