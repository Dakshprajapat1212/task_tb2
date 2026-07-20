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
use App\Models\AssignHomework;
use App\Models\SubmitHomework;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // 1. Truncate existing tables to avoid duplicate entries and clean up faker data
        $this->command->info('Cleaning database...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Faculty::truncate();
        Student::truncate();
        ClassModel::truncate();
        Subject::truncate();
        Chapter::truncate();
        TopicNote::truncate();
        QuizQuestion::truncate();
        Enrollment::truncate();
        Recording::truncate();
        StudentNoteProgress::truncate();
        QuizAttempt::truncate();
        \App\Models\Event::truncate();
        \App\Models\Announcement::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Create Roles
        $this->command->info('Creating Roles...');
        DB::table('mas_roles')->insertOrIgnore([
            ['id' => 1, 'name' => 'student'],
            ['id' => 2, 'name' => 'faculty'],
            ['id' => 3, 'name' => 'admin'],
        ]);

        // 3. Create Admin
        $this->command->info('Creating Admin...');
        User::create([
            'role_id' => 3,
            'name' => 'Admin',
            'email' => 'admin@tasktutorials.com',
            'password' => Hash::make('Password@123'),
            'phone_no' => '9999999999'
        ]);

        // 4. Create Faculty
        $this->command->info('Creating Faculty...');
        $faculties = [];
        $facultyNames = ['Mr. Ravi Sharma', 'Ms. Priya Nair', 'Shakshi Sharma'];
        for ($i = 1; $i <= 3; $i++) {
            $user = User::create([
                'role_id' => 2,
                'name' => $facultyNames[$i - 1],
                'email' => "faculty{$i}@tasktutorials.com",
                'password' => Hash::make('Password@123'),
                'phone_no' => '88888888' . $i
            ]);
            $faculties[] = Faculty::create([
                'user_id' => $user->id,
                'qualification' => 'PhD in Education',
                'date_of_joining' => now(),
            ]);
        }

        // 5. Create Students
        $this->command->info('Creating Students...');
        $students = [];
        for ($i = 1; $i <= 15; $i++) {
            $user = User::create([
                'role_id' => 1,
                'name' => "Student {$i}",
                'email' => "student{$i}@tasktutorials.com",
                'password' => Hash::make('Password@123'),
                'phone_no' => '77777777' . str_pad($i, 2, '0', STR_PAD_LEFT)
            ]);
            $students[] = Student::create([
                'user_id' => $user->id,
                'dob' => now()->subYears(15),
                'address' => '123 Test Ave, City Branch',
            ]);
        }

        // 6. Structured Curriculum Map (CBSE/ICSE Style)
        $curriculum = [
            'Grade 8' => [
                'Mathematics' => [
                    [
                        'title' => 'Rational Numbers',
                        'desc' => 'Properties of rational numbers, representation on number line, and finding rational numbers between two rational numbers.',
                        'notes' => [
                            ['title' => 'Rational Numbers Basics & Core Concepts', 'file' => 'rational_numbers_basics.pdf'],
                            ['title' => 'Solved Examples & NCERT Exercises', 'file' => 'rational_numbers_practice.pdf'],
                        ],
                        'questions' => [
                            ['q' => 'Which of the following is the additive inverse of 2/3?', 'a' => '-2/3', 'b' => '3/2', 'c' => '-3/2', 'd' => '1', 'opt' => 'a', 'ans' => '-2/3'],
                            ['q' => 'What is the multiplicative identity for rational numbers?', 'a' => '0', 'b' => '1', 'c' => '-1', 'd' => 'None', 'opt' => 'b', 'ans' => '1'],
                        ]
                    ],
                    [
                        'title' => 'Exponents and Powers',
                        'desc' => 'Laws of exponents, negative exponents, and standard form representation of very small or large numbers.',
                        'notes' => [
                            ['title' => 'Exponents Laws & Formulas', 'file' => 'exponents_laws.pdf'],
                            ['title' => 'Powers Practice Worksheet', 'file' => 'powers_practice_problems.pdf'],
                        ],
                        'questions' => [
                            ['q' => 'What is the value of 2^-3?', 'a' => '8', 'b' => '-8', 'c' => '1/8', 'd' => '-1/8', 'opt' => 'c', 'ans' => '1/8'],
                            ['q' => 'What is the product of 5^2 and 5^3?', 'a' => '5^5', 'b' => '5^6', 'c' => '25^5', 'd' => '25^6', 'opt' => 'a', 'ans' => '5^5'],
                        ]
                    ]
                ],
                'Biology' => [
                    [
                        'title' => 'Crop Production & Management',
                        'desc' => 'Agricultural practices, preparation of soil, sowing, adding manure and fertilizers, irrigation, harvesting, and storage.',
                        'notes' => [
                            ['title' => 'Agricultural Practices Overview', 'file' => 'agricultural_practices_overview.pdf'],
                            ['title' => 'Soil Preparation & Irrigation Guide', 'file' => 'soil_preparation_irrigation_guide.pdf'],
                        ],
                        'questions' => [
                            ['q' => 'Which of the following is a Kharif crop?', 'a' => 'Wheat', 'b' => 'Paddy', 'c' => 'Gram', 'd' => 'Mustard', 'opt' => 'b', 'ans' => 'Paddy'],
                        ]
                    ]
                ]
            ],
            'Grade 9' => [
                'Mathematics' => [
                    [
                        'title' => 'Number Systems',
                        'desc' => 'Introduction to irrational numbers, real numbers, decimal expansions, and rationalizing the denominator.',
                        'notes' => [
                            ['title' => 'Real Numbers & Decimal Expansion', 'file' => 'real_numbers_decimal_expansion.pdf'],
                            ['title' => 'Rationalization Practice Sheet', 'file' => 'rationalization_techniques.pdf'],
                        ],
                        'questions' => [
                            ['q' => 'Every rational number is a:', 'a' => 'Natural number', 'b' => 'Whole number', 'c' => 'Real number', 'd' => 'Integer', 'opt' => 'c', 'ans' => 'Real number'],
                        ]
                    ]
                ],
                'Physics' => [
                    [
                        'title' => 'Motion',
                        'desc' => 'Describing motion, speed with direction, rate of change of velocity, and equations of motion by graphical method.',
                        'notes' => [
                            ['title' => 'Equations of Motion: Graphical Method', 'file' => 'motion_equations_graphical.pdf'],
                            ['title' => 'Speed, Velocity & Acceleration Guide', 'file' => 'speed_velocity_acceleration_guide.pdf'],
                        ],
                        'questions' => [
                            ['q' => 'What is the SI unit of acceleration?', 'a' => 'm/s', 'b' => 'm/s^2', 'c' => 'km/h', 'd' => 'm', 'opt' => 'b', 'ans' => 'm/s^2'],
                        ]
                    ]
                ]
            ],
            'Grade 10' => [
                'Mathematics' => [
                    [
                        'title' => 'Real Numbers',
                        'desc' => 'Euclid\'s division lemma, fundamental theorem of arithmetic, and irrational proofs.',
                        'notes' => [
                            ['title' => 'Concept Summary & Euclid\'s Lemma', 'file' => 'concept_real_numbers.pdf'],
                            ['title' => 'Solved Examples & NCERT Exercises', 'file' => 'solved_real_numbers.pdf'],
                        ],
                        'questions' => [
                            ['q' => 'Which of the following is an irrational number?', 'a' => 'sqrt(2)', 'b' => '2', 'c' => '0.5', 'd' => '2/3', 'opt' => 'a', 'ans' => 'sqrt(2)'],
                            ['q' => 'What is the HCF of 24 and 36?', 'a' => '6', 'b' => '12', 'c' => '18', 'd' => '24', 'opt' => 'b', 'ans' => '12'],
                        ]
                    ],
                    [
                        'title' => 'Polynomials',
                        'desc' => 'Geometrical meaning of zeroes, division algorithm, and relationships between coefficients.',
                        'notes' => [
                            ['title' => 'Polynomial Core Formulas & Rules', 'file' => 'poly_formulas.pdf'],
                            ['title' => 'Division Algorithm Practice Worksheet', 'file' => 'poly_practice.pdf'],
                        ],
                        'questions' => [
                            ['q' => 'What is the degree of a quadratic polynomial?', 'a' => '1', 'b' => '2', 'c' => '3', 'd' => '4', 'opt' => 'b', 'ans' => '2'],
                        ]
                    ],
                    [
                        'title' => 'Quadratic Equations',
                        'desc' => 'Standard form, solutions by factorization and quadratic formula, nature of roots.',
                        'notes' => [
                            ['title' => 'Nature of Roots & Discriminant Guide', 'file' => 'quad_roots.pdf'],
                            ['title' => 'Formula Sheet & Solved Problems', 'file' => 'quad_problems.pdf'],
                        ],
                        'questions' => [
                            ['q' => 'If discriminant D > 0, the roots of quadratic equation are:', 'a' => 'Real and equal', 'b' => 'Real and unequal', 'c' => 'Imaginary', 'd' => 'No roots', 'opt' => 'b', 'ans' => 'Real and unequal'],
                        ]
                    ]
                ],
                'Physics' => [
                    [
                        'title' => 'Light - Reflection and Refraction',
                        'desc' => 'Spherical mirrors, mirror formula, magnification, refraction, and refractive index.',
                        'notes' => [
                            ['title' => 'Ray Diagrams & Sign Conventions', 'file' => 'light_ray_diagrams.pdf'],
                            ['title' => 'Lens Formula & Numerical Practice', 'file' => 'light_numericals.pdf'],
                        ],
                        'questions' => [
                            ['q' => 'The focal length of a spherical mirror of radius of curvature 30 cm is:', 'a' => '10 cm', 'b' => '15 cm', 'c' => '30 cm', 'd' => '60 cm', 'opt' => 'b', 'ans' => '15 cm'],
                        ]
                    ]
                ]
            ],
            'Grade 11' => [
                'Chemistry' => [
                    [
                        'title' => 'Basic Concepts of Chemistry',
                        'desc' => 'Scope of chemistry, laws of chemical combination, Dalton\'s atomic theory, atomic and molecular masses.',
                        'notes' => [
                            ['title' => 'Mole Concept & Molarity Calculations', 'file' => 'mole_concept_molarity.pdf'],
                            ['title' => 'Empirical & Molecular Formula Guide', 'file' => 'empirical_molecular_formula.pdf'],
                        ],
                        'questions' => [
                            ['q' => 'What is the molar mass of water (H2O)?', 'a' => '18 g/mol', 'b' => '16 g/mol', 'c' => '2 g/mol', 'd' => '20 g/mol', 'opt' => 'a', 'ans' => '18 g/mol'],
                        ]
                    ]
                ]
            ],
            'Grade 12' => [
                'Mathematics' => [
                    [
                        'title' => 'Matrices & Determinants',
                        'desc' => 'Matrix operations, transpose, symmetric matrices, inverses, and Cramer\'s Rule.',
                        'notes' => [
                            ['title' => 'Properties of Determinants', 'file' => 'determinants_properties.pdf'],
                            ['title' => 'Matrix Inversion & System Solutions', 'file' => 'matrix_inverse.pdf']
                        ],
                        'questions' => [
                            ['q' => 'If A is a square matrix of order 3, and |A| = 5, what is |adj A|?', 'a' => '5', 'b' => '25', 'c' => '125', 'd' => '10', 'opt' => 'b', 'ans' => '25']
                        ]
                    ],
                    [
                        'title' => 'Calculus - Limits & Continuity',
                        'desc' => 'Concepts of limits, continuity, differentiability, and standard derivatives.',
                        'notes' => [
                            ['title' => 'Standard Derivative Formula Reference', 'file' => 'calculus_formulas.pdf'],
                            ['title' => 'Practice Sheet: Limits & Differentiability', 'file' => 'calculus_limits.pdf']
                        ],
                        'questions' => [
                            ['q' => 'What is the derivative of sin(x²)?', 'a' => 'cos(x²)', 'b' => '2x * cos(x²)', 'c' => '2 * cos(x)', 'd' => '-cos(x²)', 'opt' => 'b', 'ans' => '2x * cos(x²)']
                        ]
                    ]
                ],
                'Physics' => [
                    [
                        'title' => 'Electrostatics',
                        'desc' => 'Electric charges, Coulomb\'s law, electric fields, dipoles, and Gauss\'s theorem.',
                        'notes' => [
                            ['title' => 'Coulomb\'s Law & Field Lines Guide', 'file' => 'electrostatics_fields.pdf'],
                            ['title' => 'Gauss\'s Theorem & Application Notes', 'file' => 'gauss_applications.pdf']
                        ],
                        'questions' => [
                            ['q' => 'The electric field inside a perfectly conducting hollow sphere is:', 'a' => 'Infinite', 'b' => 'Zero', 'c' => 'Constant', 'd' => 'Depends on radius', 'opt' => 'b', 'ans' => 'Zero']
                        ]
                    ]
                ]
            ]
        ];

        // 7. Seed Classes, Subjects, Chapters, Notes, and Quiz Questions
        $this->command->info('Creating Curriculum...');
        $classes = [];
        $subjectsMap = []; // Keep track of created subject models by name

        foreach ($curriculum as $className => $subjectsData) {
            // Create Class
            $classModel = ClassModel::create(['name' => $className]);
            $classes[] = $classModel;

            foreach ($subjectsData as $subjectName => $chaptersData) {
                // Fetch or Create Subject
                if (!isset($subjectsMap[$subjectName])) {
                    $subjectsMap[$subjectName] = Subject::create(['name' => $subjectName]);
                }
                $subjectModel = $subjectsMap[$subjectName];

                // Assign subject to class with schedule
                $faculty = $faculties[array_rand($faculties)];
                $classModel->subjects()->attach($subjectModel->id, [
                    'faculty_id' => $faculty->id,
                    'class_link' => 'https://meet.google.com/xyz-fake-link-' . rand(100, 999),
                    'class_date' => \Carbon\Carbon::today()->addDays(rand(1, 15))->format('Y-m-d'),
                    'start_time' => '10:00:00',
                    'end_time'   => '12:00:00',
                    'stream_url' => '/chemistry_lecture.mp4'
                ]);

                // Create Chapters
                foreach ($chaptersData as $chIndex => $chData) {
                    $chapterModel = Chapter::create([
                        'class_id' => $classModel->id,
                        'subject_id' => $subjectModel->id,
                        'title' => $chData['title'],
                        'description' => $chData['desc'],
                        'display_order' => $chIndex + 1,
                        'status' => 'active'
                    ]);

                    // Seed 2 Recordings for this Chapter (one with chapters timeline, one without)
                    Recording::create([
                        'class_id' => $classModel->id,
                        'subject_id' => $subjectModel->id,
                        'chapter_id' => $chapterModel->id,
                        'topic' => 'Lecture 1: Introduction to ' . $chapterModel->title,
                        'teacher_name' => $faculty->user->name,
                        'duration' => rand(30, 45),
                        'video_link' => '/chemistry_lecture.mp4',
                        'chapters' => [
                            ['name' => 'Introduction & Base Definition', 'time' => '00:00', 'sec' => 0],
                            ['name' => 'Core Rules & Formula Overview', 'time' => '05:10', 'sec' => 310],
                            ['name' => 'Classroom Exercises', 'time' => '12:45', 'sec' => 765],
                            ['name' => 'Summary & Q&A', 'time' => '20:15', 'sec' => 1215]
                        ]
                    ]);

                    Recording::create([
                        'class_id' => $classModel->id,
                        'subject_id' => $subjectModel->id,
                        'chapter_id' => $chapterModel->id,
                        'topic' => 'Lecture 2: Practice Problems on ' . $chapterModel->title,
                        'teacher_name' => $faculty->user->name,
                        'duration' => rand(25, 40),
                        'video_link' => '/chemistry_lecture.mp4',
                        'chapters' => null // No timeline chapters (hides tab)
                    ]);

                    // Seed Homework assignments for this chapter
                    if ($chIndex === 0) {
                        AssignHomework::create([
                            'class_id' => $classModel->id,
                            'subject_id' => $subjectModel->id,
                            'topic' => 'Practice Set 1: Basics of ' . $chapterModel->title,
                            'description' => 'Complete all exercises in the chapter review for ' . $chapterModel->title . '. Show your step-by-step solutions clearly.',
                            'due_date' => \Carbon\Carbon::today()->addDays(5)->format('Y-m-d'),
                            'status' => 'active',
                            'points' => 100,
                            'xp' => 50
                        ]);

                        AssignHomework::create([
                            'class_id' => $classModel->id,
                            'subject_id' => $subjectModel->id,
                            'topic' => 'Assigned Challenge: ' . $chapterModel->title,
                            'description' => 'Solve the advanced problems of ' . $chapterModel->title . ' on page 34 of your reference book.',
                            'due_date' => \Carbon\Carbon::today()->subDays(2)->format('Y-m-d'),
                            'status' => 'active',
                            'points' => 100,
                            'xp' => 50
                        ]);
                    }

                    // Create Topic Notes
                    foreach ($chData['notes'] as $nIndex => $nData) {
                        $topicNoteModel = TopicNote::create([
                            'class_id' => $classModel->id,
                            'subject_id' => $subjectModel->id,
                            'chapter_id' => $chapterModel->id,
                            'chapter' => $nData['title'],
                            'file_url' => 'task_tutorials_dummy.pdf'
                        ]);

                        // Seed Questions for the Topic Note
                        foreach ($chData['questions'] as $qIndex => $qData) {
                            QuizQuestion::create([
                                'chapter_id' => $chapterModel->id,
                                'topic_note_id' => $topicNoteModel->id,
                                'question' => $qData['q'],
                                'option_a' => $qData['a'],
                                'option_b' => $qData['b'],
                                'option_c' => $qData['c'],
                                'option_d' => $qData['d'],
                                'correct_option' => $qData['opt'],
                                'correct_answer' => $qData['ans'],
                                'difficulty_level' => $qIndex === 0 ? 'Easy' : 'Medium'
                            ]);
                        }

                        // Generate Note Progress for random students
                        foreach (array_rand($students, 5) as $studentIdx) {
                            StudentNoteProgress::create([
                                'student_id' => $students[$studentIdx]->id,
                                'topic_note_id' => $topicNoteModel->id,
                                'completed_at' => now(),
                            ]);
                        }
                    }
                }
            }
        }

        // 8. Enroll Students
        $this->command->info('Enrolling Students...');
        foreach ($students as $student) {
            $randomClasses = array_rand($classes, 2);
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

        $this->command->info('Seeding Demo Submissions...');
        $allHomeworks = AssignHomework::all();
        foreach ($allHomeworks as $hwIndex => $hw) {
            $student = $students[0];
            if ($hwIndex % 2 === 0) {
                SubmitHomework::firstOrCreate(
                    [
                        'assign_homework_id' => $hw->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'file' => 'task_tutorials_dummy.pdf',
                        'graded_file' => 'task_tutorials_dummy.pdf',
                        'status' => 'approved',
                        'remarks' => 'Great problem-solving steps! Excellent work on step 3. 95/100.',
                        'student_comment' => 'Completed all exercises from the chapter review.'
                    ]
                );
            }
        }

        // 9. Seed Events and Announcements
        $this->command->info('Seeding Events and Announcements...');
        $this->call([
            EventSeeder::class,
            AnnouncementSeeder::class,
        ]);

        $this->command->info('Database Seeded Successfully!');
    }
}
