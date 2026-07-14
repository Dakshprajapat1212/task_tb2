<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Chapter;
use App\Models\TopicNote;
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
            ['id' => 1, 'name' => 'student'],
            ['id' => 2, 'name' => 'faculty'],
            ['id' => 3, 'name' => 'admin'],
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
            $user = User::factory()->create(['role_id' => 2, 'email' => "faculty{$i}@tasktutorials.com", 'password' => Hash::make('Password@123')]);
            $faculties[] = Faculty::create([
                'user_id' => $user->id,
                'qualification' => 'PhD in Education',
                'date_of_joining' => now(),
            ]);
        }

        $this->command->info('Creating Students...');
        $students = [];
        for ($i = 1; $i <= 30; $i++) {
            $user = User::factory()->create(['role_id' => 1, 'email' => "student{$i}@tasktutorials.com", 'password' => Hash::make('Password@123')]);
            $students[] = Student::create([
                'user_id' => $user->id,
                'dob' => now()->subYears(15),
                'address' => '123 Test Ave',
            ]);
        }

        $this->command->info('Creating Classes, Subjects, Chapters, Notes, and Quizzes...');
        $classes = [];
        for ($c = 1; $c <= 5; $c++) {
            $faculty = $faculties[array_rand($faculties)];
            
            $mockSubjects = [
                'Simple Algebra',
                'Exponents',
                'Matter And Exponents',
                'Rational Number',
                'Fundamental Concept',
                'Physical & Chemical Changes',
                'Transportation in Plants'
            ];
            
            $subjects = [];
            foreach (array_rand(array_flip($mockSubjects), 5) as $mockSubjectName) {
                $subjects[] = Subject::factory()->create([
                    'name' => $mockSubjectName
                ]);
            }

            $classModel = ClassModel::factory()->create([
                'name' => "Grade " . $faker->numberBetween(8, 12),
            ]);
            $classes[] = $classModel;

            foreach ($subjects as $index => $subject) {
                // Determine a faculty to assign for this subject
                $faculty = $faculties[array_rand($faculties)];
                $classModel->subjects()->attach($subject->id, [
                    'faculty_id' => $faculty->id,
                    'class_link' => 'https://meet.google.com/xyz-fake-link-'.rand(100, 999),
                    'class_date' => \Carbon\Carbon::today()->addDays(rand(1, 30))->format('Y-m-d'),
                    'start_time' => '10:00:00',
                    'end_time'   => '12:00:00'
                ]);

                // Create Chapters
                for ($ch = 1; $ch <= 3; $ch++) {
                    $chapter = Chapter::factory()->create([
                        'class_id' => $classModel->id,
                        'subject_id' => $subject->id,
                    ]);

                    // Question Bank for Chapter
                    $this->createQuestions($chapter->id, null);

                    // Create Topic Notes
                    for ($n = 1; $n <= 2; $n++) {
                        $topicNote = TopicNote::factory()->create([
                            'class_id' => $classModel->id,
                            'subject_id' => $subject->id,
                            'chapter_id' => $chapter->id,
                        ]);

                        // Question Bank for Topic Note
                        $this->createQuestions($chapter->id, $topicNote->id);

                        // Progress for random students
                        foreach(array_rand($students, 5) as $studentIdx) {
                            StudentNoteProgress::create([
                                'student_id' => $students[$studentIdx]->id,
                                'topic_note_id' => $topicNote->id,
                                'completed_at' => now(),
                            ]);
                        }
                    }
                }
            }
        }

        $this->command->info('Enrolling Students...');
        foreach ($students as $student) {
            $randomClasses = array_rand($classes, 3);
            foreach ((array)$randomClasses as $classIdx) {
                Enrollment::create([
                    'user_id' => $student->user_id,
                    'class_id' => $classes[$classIdx]->id,
                    'dob' => $student->dob,
                    'address' => $student->address,
                    'status' => 'approved'
                ]);
            }
        }
        
        $this->command->info('Creating Recordings...');
        $mockLectures = [
            'Lecture 1: Introduction to Power Rules',
            'Lecture 2: Base and Exponents Definition',
            'Lecture 3: Multiplying Powers with Same Base',
            'Lecture 4: Advanced Exponent Problems',
            'Lecture 5: Class Practice Problems'
        ];
        
        foreach ($classes as $class) {
            foreach ($mockLectures as $lectureTitle) {
                Recording::create([
                    'class_id' => $class->id,
                    'topic' => $lectureTitle,
                    'duration' => 60,
                    'video_link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
                ]);
            }
        }

        $this->command->info('Database Seeded Successfully!');
    }

    private function createQuestions($chapterId, $topicNoteId)
    {
        $mockQuestions = [
            'How to find sin 30°?',
            'What is the value of 2³ * 2⁴?',
            'Calculate the derivative of x².',
            'What is the formula for water?',
            'Who developed the theory of relativity?',
            'What is the speed of light?',
            'Solve for x: 2x = 10',
            'Define photosynthesis.',
            'What is Avogadro\'s number?',
            'State Newton\'s First Law.',
            'What is the area of a circle with radius r?',
            'Explain the Pythagorean theorem.',
            'What is the powerhouse of the cell?',
            'What is a prime number?',
            'Solve: 5 + 3 * 2',
            'What is the boiling point of water?',
            'Define kinetic energy.',
            'What is a covalent bond?',
            'Explain the water cycle.',
            'What is an exponent?'
        ];

        foreach ($mockQuestions as $index => $questionText) {
            QuizQuestion::factory()->create([
                'chapter_id' => $chapterId,
                'topic_note_id' => $topicNoteId,
                'question' => $questionText,
                'difficulty_level' => match(true) {
                    $index <= 5 => 'Easy',
                    $index <= 12 => 'Medium',
                    default => 'Hard'
                }
            ]);
        }
    }
}
