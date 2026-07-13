<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TopicNote;

class TopicNoteFactory extends Factory
{
    protected $model = TopicNote::class;

    public function definition(): array
    {
        return [
            'chapter' => fake()->sentence(4), // Maps to 'title'
            'file_url' => 'notes/sample_' . fake()->numberBetween(1, 10) . '.pdf',
            // class_id, subject_id, chapter_id provided during creation
        ];
    }
}