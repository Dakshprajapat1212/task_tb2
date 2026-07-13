<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class QuizFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3) . ' Assessment',
            'passing_marks' => fake()->numberBetween(40, 80),
            // chapter_id, topic_note_id provided during creation
        ];
    }
}