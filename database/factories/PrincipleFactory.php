<?php

namespace Database\Factories;

use App\Models\Principle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Principle>
 */
class PrincipleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'acronym' => strtoupper(fake()->lexify('???')),
            'category' => fake()->randomElement(['SOLID', 'Arquitetura', 'Boas práticas']),
            'summary' => fake()->sentence(),
            'description' => fake()->paragraph(),
        ];
    }
}
