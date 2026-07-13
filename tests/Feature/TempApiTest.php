<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Models\Note;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TempApiTest extends TestCase
{
    use RefreshDatabase; // Use refresh database to ensure clean state and no duplicate issues

    public function test_api_report()
    {
        // 1. Create Minimal Test Data
        $role = \App\Models\MasRole::create(['name' => 'student']);
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test' . rand() . '@example.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id
        ]);
        $student = Student::create(['user_id' => $user->id, 'dob' => '2000-01-01', 'address' => '123 Fake St']);
        
        $facultyRole = \App\Models\MasRole::create(['name' => 'faculty']);
        $facultyUser = User::create([
            'name' => 'Faculty User',
            'email' => 'faculty' . rand() . '@example.com',
            'password' => bcrypt('password'),
            'role_id' => $facultyRole->id
        ]);
        $faculty = \App\Models\Faculty::create([
            'user_id' => $facultyUser->id,
            'date_of_joining' => '2020-01-01',
            'qualification' => 'PhD'
        ]);

        $subject = Subject::create(['name' => 'Math', 'description' => 'Math', 'status' => 'active', 'faculty_id' => $faculty->id]);
        $class = ClassModel::create(['name' => 'Class 1', 'subject_id' => $subject->id, 'status' => 'active', 'faculty_id' => $faculty->id, 'class_link' => 'http://example.com', 'class_date' => '2026-01-01', 'start_time' => '10:00:00', 'end_time' => '11:00:00']);
        
        // Approve Enrollment
        Enrollment::create([
            'user_id' => $user->id,
            'class_id' => $class->id,
            'status' => 'approved',
            'dob' => '2000-01-01',
            'address' => '123 Fake St'
        ]);

        $topic = Topic::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'title' => 'Topic 1',
            'status' => 'active'
        ]);

        $note = Note::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'topic_id' => $topic->id,
            'topic' => 'Module Note 1',
            'file_url' => 'fake.pdf'
        ]);

        // Legacy Quiz (Topic Level)
        $legacyQuiz = Quiz::create([
            'topic_id' => $topic->id,
            'title' => 'Legacy Quiz',
            'passing_marks' => 1
        ]);
        $legacyQuestion = QuizQuestion::create([
            'quiz_id' => $legacyQuiz->id,
            'question' => '1+1?',
            'option_a' => '1', 'option_b' => '2', 'option_c' => '3', 'option_d' => '4',
            'correct_option' => 'b'
        ]);

        // New Quiz (Note Level / Question Bank)
        $moduleQuiz = Quiz::create([
            'topic_id' => $topic->id, // required legacy fallback
            'note_id' => $note->id,
            'title' => 'Module Quiz Bank',
            'passing_marks' => 1
        ]);
        $moduleQuestion = QuizQuestion::create([
            'quiz_id' => $moduleQuiz->id,
            'question' => '2+2?',
            'option_a' => '2', 'option_b' => '3', 'option_c' => '4', 'option_d' => '5',
            'correct_option' => 'c'
        ]);

        $report = [];

        // Helper to run request and collect report
        $runTest = function ($method, $url, $payload = []) use ($user, &$report) {
            try {
                $response = $this->actingAs($user, 'sanctum')->json($method, $url, $payload);
                $report[] = [
                    'url' => $url,
                    'method' => $method,
                    'status' => $response->status(),
                    'response' => json_decode($response->content(), true) ?: $response->content(),
                    'error' => null
                ];
            } catch (\Exception $e) {
                $report[] = [
                    'url' => $url,
                    'method' => $method,
                    'status' => 500,
                    'response' => null,
                    'error' => $e->getMessage()
                ];
            }
        };

        // 2. Run Tests
        // EXISTING APIs
        $runTest('GET', '/api/library/classes');
        $runTest('GET', "/api/library/classes/{$class->id}/subjects");
        $runTest('GET', "/api/library/class-subjects/{$class->id}/topics");
        $runTest('GET', "/api/library/topics/{$topic->id}/notes");
        $runTest('GET', "/api/library/topics/{$topic->id}/quiz"); // Legacy Quiz endpoint
        
        $runTest('POST', "/api/library/quizzes/{$legacyQuiz->id}/submit", [
            'answers' => [
                ['question_id' => $legacyQuestion->id, 'selected_option' => 'b']
            ]
        ]);
        $runTest('GET', "/api/library/quizzes/{$legacyQuiz->id}/result");

        // NEW APIs
        $runTest('GET', "/api/library/modules/{$note->id}/quiz");
        $runTest('GET', "/api/library/modules/{$note->id}/flashcards");

        // NEW Submission Flow (Submit new module quiz)
        $runTest('POST', "/api/library/quizzes/{$moduleQuiz->id}/submit", [
            'answers' => [
                ['question_id' => $moduleQuestion->id, 'selected_option' => 'c']
            ]
        ]);
        $runTest('GET', "/api/library/quizzes/{$moduleQuiz->id}/result");

        // V2 APIs
        $runTest('GET', "/api/v2/library/class-subjects/{$class->id}/chapters");
        $runTest('GET', "/api/v2/library/chapters/{$topic->id}/topic-notes");
        $runTest('GET', "/api/v2/library/chapters/{$topic->id}/question-bank");
        $runTest('GET', "/api/v2/library/topic-notes/{$note->id}/question-bank");

        file_put_contents(base_path('api_test_report.json'), json_encode($report, JSON_PRETTY_PRINT));
        
        $this->assertTrue(true);
    }
}
