<?php

namespace Database\Factories;

use App\Models\BragEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BragEntry>
 */
class BragEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'goal' => fake()->sentence(4),
            'deadline' => fake()->randomElement(['Q3 2026', 'Dez/2026']),
            'project' => fake()->words(2, true),
            'contribution' => fake()->paragraph(),
            'impact_changed' => fake()->sentence(),
        ];
    }
}
