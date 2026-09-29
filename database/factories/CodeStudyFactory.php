<?php

namespace Database\Factories;

use App\Models\CodeStudy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CodeStudy>
 */
class CodeStudyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'topic' => fake()->word(),
            'principle_id' => null,
            'description' => fake()->paragraph(),
        ];
    }
}
