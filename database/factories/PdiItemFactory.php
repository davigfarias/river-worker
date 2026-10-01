<?php

namespace Database\Factories;

use App\Models\PdiItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PdiItem>
 */
class PdiItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'objective' => fake()->sentence(4),
            'current_situation' => fake()->sentence(),
            'action' => fake()->paragraph(),
            'measurement' => fake()->sentence(),
            'deadline' => fake()->randomElement(['Q4 2026', 'Dez/2026']),
        ];
    }
}
