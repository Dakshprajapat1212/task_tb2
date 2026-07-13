<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Mathematics', 'Physics', 'Chemistry', 'Biology', 'Computer Science', 'History', 'Geography', 'English']),
            // faculty_id provided during creation
        ];
    }
}