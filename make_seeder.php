<?php
$seeder = <<<'PHP'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Note;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Enrollment;
use App\Models\Recording;
use App\Models\StudentNoteProgress;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $this->command->info('Creating Roles...');
        DB::table('mas_roles')->insertOrIgnore([
            ['id' => 1, 'role_name' => 'student'],
            ['id' => 2, 'role_name' => 'faculty'],
            ['id' => 3, 'role_name' => 'admin'],
        ]);

        $this->command->info('Creating Admin...');
        User::create([
            'role_id' => 3,
            'name' => 'Admin',
            'email' => 'admin@tasktutorials.com',
            'password' => Hash::make('Password@123'),
            'phone_no' => '9999999999'
        ]);

        $this->command->info('Creating Faculty...');
        $faculties = [];
        for ($i = 1; $i <= 3; $i++) {
            $user = User::factory()->create(['role_id' => 2, 'email' => "faculty{$i}@tasktutorials.com"]);
            $faculties[] = Faculty::create([
                'user_id' => $user->id,
                'designation' => 'Senior Lecturer',
                'joining_date' => now(),
            ]);
        }

        $this->command->info('Creating Students...');
        $students = [];
        for ($i = 1; $i <= 30; $i++) {
            $user = User::factory()->create(['role_id' => 1, 'email' => "student{$i}@tasktutorials.com"]);
            $students[] = Student::create([
                'user_id' => $user->id,
                'admission_date' => now(),
                'status' => 'active',
            ]);
        }

        $this->command->info('Creating Classes, Subjects, Chapters, Notes, and Quizzes...');
        $classes = [];
        for ($c = 1; $c <= 5; $c++) {
            $faculty = $faculties[array_rand($faculties)];
            
            // Wait, ClassModel needs a subject_id. In the legacy schema, Class is tied to Subject, or Subject is tied to Faculty?
            // Actually, TempApiTest says: Subject::create, then ClassModel::create(['subject_id' => subject->id])
            // Let's create subjects first.
            $subjects = [];
            for ($s = 1; $s <= 5; $s++) {
                $subjects[] = Subject::factory()->create(['faculty_id' => $faculty->id]);
            }

            foreach ($subjects as $subject) {
                $class = ClassModel::factory()->create([
                    'faculty_id' => $faculty->id,
                    'subject_id' => $subject->id,
                    'name' => "Grade " . $faker->numberBetween(8, 12) . " " . $subject->name,
                ]);
                $classes[] = $class;

                // Create Chapters
                for ($ch = 1; $ch <= 3; $ch++) {
                    $topic = Topic::factory()->create([
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                    ]);

                    // Question Bank for Chapter
                    $chapterQuiz = Quiz::factory()->create(['topic_id' => $topic->id, 'note_id' => null]);
                    $this->createQuestions($chapterQuiz);

                    // Create Topic Notes
                    for ($n = 1; $n <= 2; $n++) {
                        $note = Note::factory()->create([
                            'class_id' => $class->id,
                            'subject_id' => $subject->id,
                            'topic_id' => $topic->id,
                        ]);

                        // Question Bank for Topic Note
                        $noteQuiz = Quiz::factory()->create(['topic_id' => $topic->id, 'note_id' => $note->id]);
                        $this->createQuestions($noteQuiz);

                        // Progress for random students
                        foreach(array_rand($students, 5) as $studentIdx) {
                            StudentNoteProgress::create([
                                'student_id' => $students[$studentIdx]->id,
                                'note_id' => $note->id,
                                'is_completed' => true,
                                'completed_at' => now(),
                            ]);
                        }
                    }
                }
            }
        }

        $this->command->info('Enrolling Students...');
        foreach ($students as $student) {
            // Enroll in 3 random classes
            $randomClasses = array_rand($classes, 3);
            foreach ((array)$randomClasses as $classIdx) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'class_id' => $classes[$classIdx]->id,
                    'status' => 'approved'
                ]);
            }
        }
        
        $this->command->info('Creating Recordings...');
        foreach ($classes as $class) {
            Recording::create([
                'class_id' => $class->id,
                'faculty_id' => $class->faculty_id,
                'topic' => 'Live Session Recording',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
            ]);
        }

        $this->command->info('Database Seeded Successfully!');
    }

    private function createQuestions($quiz)
    {
        // Generate exactly 20 questions
        for ($q = 1; $q <= 20; $q++) {
            QuizQuestion::factory()->create([
                'quiz_id' => $quiz->id,
                'difficulty_level' => match(true) {
                    $q <= 6 => 'Easy',
                    $q <= 14 => 'Medium',
                    default => 'Hard'
                }
            ]);
        }
    }
}
PHP;

file_put_contents(__DIR__ . '/database/seeders/DatabaseSeeder.php', $seeder);
echo "DatabaseSeeder rewritten\n";
