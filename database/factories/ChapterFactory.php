<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chapter;
class ChapterFactory extends Factory
{
    protected $model = Chapter::class;
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'display_order' => fake()->numberBetween(1, 100),
            'status' => 'active',
            // class_id and subject_id provided during creation
        ];
    }
}