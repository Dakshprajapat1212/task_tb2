<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class QuizQuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question' => fake()->sentence() . '?',
            'option_a' => fake()->word(),
            'option_b' => fake()->word(),
            'option_c' => fake()->word(),
            'option_d' => fake()->word(),
            'correct_option' => fake()->randomElement(['a', 'b', 'c', 'd']),
            'difficulty_level' => fake()->randomElement(['Easy', 'Medium', 'Hard']),
            'explanation' => fake()->paragraph(),
            'display_order' => fake()->numberBetween(1, 100),
            // quiz_id provided during creation
        ];
    }
}