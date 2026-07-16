<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\QuizQuestion;
use App\Models\Doubt;
use App\Models\User;

class DoubtFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $chapterId = 1; // Assuming testing on Chapter 1
        $faculty = User::first();
        $student = User::orderBy('id', 'desc')->first();

        // ----------------------------------------------------
        // SEED QUESTION BANK (quiz_questions)
        // ----------------------------------------------------
        $questions = [
            [
                'question' => 'What is compile time polymorphism in Object Oriented Programming?',
                'correct_answer' => 'Method Overloading',
                'explanation' => 'Compile time polymorphism is achieved using method overloading, where multiple methods have the same name but different parameters.'
            ],
            [
                'question' => 'What are the main types of polymorphism?',
                'correct_answer' => 'Compile time and Runtime',
                'explanation' => 'The two main types of polymorphism in OOP are compile time (static) and runtime (dynamic).'
            ],
            [
                'question' => 'Which keyword is associated with runtime polymorphism in Java?',
                'correct_answer' => 'extends (Inheritance)',
                'explanation' => 'Runtime polymorphism relies on inheritance and method overriding.'
            ],
            [
                'question' => 'What is the powerhouse of the cell?',
                'correct_answer' => 'Mitochondria',
                'explanation' => 'Mitochondria generate most of the chemical energy needed to power the cell.'
            ],
            [
                'question' => 'How does photosynthesis work in plants?',
                'correct_answer' => 'Converts light energy to chemical energy',
                'explanation' => 'Plants use sunlight, water and CO2 to create oxygen and energy in the form of sugar.'
            ]
        ];

        foreach ($questions as $idx => $q) {
            QuizQuestion::updateOrCreate(
                ['question' => $q['question'], 'chapter_id' => $chapterId],
                [
                    'correct_answer' => $q['correct_answer'],
                    'explanation' => $q['explanation'],
                    'display_order' => $idx
                ]
            );
        }

        // ----------------------------------------------------
        // SEED RESOLVED DOUBTS (doubts)
        // ----------------------------------------------------
        $doubts = [
            [
                'question' => 'How does runtime polymorphism actually work? Can you give an example?',
                'answer' => 'It is achieved using method overriding.',
                'explanation' => 'For example, if Animal has a speak() method, Dog can override speak() to bark instead.'
            ],
            [
                'question' => 'Why is polymorphism important in software architecture?',
                'answer' => 'It allows for flexibility and code reuse.',
                'explanation' => 'You can write generic code that works with objects of multiple types.'
            ],
            [
                'question' => 'Do plants perform photosynthesis at night in the dark?',
                'answer' => 'No, it pauses in the dark.',
                'explanation' => 'They require light for the light-dependent reactions, so they rely on cellular respiration at night.'
            ]
        ];

        foreach ($doubts as $d) {
            Doubt::updateOrCreate(
                ['question' => $d['question'], 'chapter_id' => $chapterId],
                [
                    'user_id' => $student->id,
                    'class_id' => 1,
                    'subject_id' => 1,
                    'status' => 'resolved',
                    'answer' => $d['answer'],
                    'explanation' => $d['explanation'],
                    'answered_by' => $faculty->id
                ]
            );
        }
    }
}
