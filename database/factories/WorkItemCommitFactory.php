<?php

namespace Database\Factories;

use App\Models\WorkItem;
use App\Models\WorkItemCommit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkItemCommit>
 */
class WorkItemCommitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sha = fake()->sha1();

        return [
            'work_item_id' => WorkItem::factory(),
            'sha' => $sha,
            'message' => 'feat: '.fake()->sentence(4),
            'author' => fake()->userName(),
            'committed_at' => now(),
            'url' => "https://github.com/acme/app/commit/{$sha}",
        ];
    }
}
