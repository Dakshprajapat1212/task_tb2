<?php
$factories = [
    'UserFactory' => <<<'PHP'
<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('Password@123'),
            'phone_no' => fake()->numerify('##########'),
            'role_id' => 1, // Student by default
        ];
    }
}
PHP,
    'ClassModelFactory' => <<<'PHP'
<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class ClassModelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word() . ' Class',
            'class_date' => fake()->date(),
            'start_time' => fake()->time(),
            'end_time' => fake()->time(),
            // faculty_id and subject_id should be provided during creation
        ];
    }
}
PHP,
    'SubjectFactory' => <<<'PHP'
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
PHP,
    'TopicFactory' => <<<'PHP'
<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class TopicFactory extends Factory
{
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
PHP,
    'NoteFactory' => <<<'PHP'
<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class NoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'topic' => fake()->sentence(4), // Maps to 'title'
            'file_url' => 'notes/sample_' . fake()->numberBetween(1, 10) . '.pdf',
            // class_id, subject_id, topic_id provided during creation
        ];
    }
}
PHP,
    'QuizFactory' => <<<'PHP'
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
            // topic_id, note_id provided during creation
        ];
    }
}
PHP,
    'QuizQuestionFactory' => <<<'PHP'
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
PHP,
];

foreach ($factories as $name => $content) {
    file_put_contents(__DIR__ . '/database/factories/' . $name . '.php', $content);
    echo "Generated $name\n";
}
