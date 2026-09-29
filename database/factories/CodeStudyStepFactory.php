<?php

namespace Database\Factories;

use App\Models\CodeStudy;
use App\Models\CodeStudyStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CodeStudyStep>
 */
class CodeStudyStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code_study_id' => CodeStudy::factory(),
            'title' => fake()->sentence(3),
            'language' => 'php',
            'snippet' => '<?php echo 1;',
            'markdown' => fake()->paragraph(),
            'position' => 0,
        ];
    }
}
