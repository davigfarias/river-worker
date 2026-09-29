<?php

namespace Database\Factories;

use App\Models\Concept;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Concept>
 */
class ConceptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(2, true),
            'definition' => fake()->sentence(),
            'code_snippet' => null,
            'principle_id' => null,
        ];
    }
}
