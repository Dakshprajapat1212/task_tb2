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
use App\Models\Faculty;
use App\Models\MasRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V2AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_v2_admin_and_test_generation_flow()
    {
        // Setup Users
        \DB::table('mas_roles')->insert([
            ['id' => 1, 'name' => 'student'],
            ['id' => 2, 'name' => 'faculty'],
            ['id' => 3, 'name' => 'admin'],
        ]);

        $adminUser = User::create([
            'name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password'), 'role_id' => 3
        ]);
        
        $facultyUser = User::create([
            'name' => 'Faculty', 'email' => 'faculty@example.com', 'password' => bcrypt('password'), 'role_id' => 2
        ]);
        $faculty = Faculty::create(['user_id' => $facultyUser->id, 'date_of_joining' => '2020-01-01', 'qualification' => 'PhD']);

        $studentUser = User::create([
            'name' => 'Student', 'email' => 'student@example.com', 'password' => bcrypt('password'), 'role_id' => 1
        ]);
        Student::create(['user_id' => $studentUser->id, 'dob' => '2000-01-01', 'address' => '123 Fake St']);

        // Setup Base Data
        $subject = Subject::create(['name' => 'Math', 'description' => 'Math', 'status' => 'active', 'faculty_id' => $faculty->id]);
        $class = ClassModel::create(['name' => 'Class 1', 'subject_id' => $subject->id, 'status' => 'active', 'faculty_id' => $faculty->id, 'class_link' => 'http://example.com', 'class_date' => '2026-01-01', 'start_time' => '10:00:00', 'end_time' => '11:00:00']);
        
        Enrollment::create(['user_id' => $studentUser->id, 'class_id' => $class->id, 'status' => 'approved', 'dob' => '2000-01-01', 'address' => '123 Fake St']);

        // 1. Admin creates a Chapter (Topic)
        $response = $this->actingAs($adminUser, 'sanctum')->postJson('/api/v2/admin/chapters', [
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'title' => 'V2 Chapter 1'
        ]);
        $response->assertStatus(201);
        $chapterId = $response->json('data.id');

        // 2. Admin creates a Topic Note (Note)
        $response = $this->actingAs($adminUser, 'sanctum')->postJson('/api/v2/admin/topic-notes', [
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapterId,
            'title' => 'V2 Note 1',
            'file_url' => 'http://fake.com/file.pdf'
        ]);
        $response->assertStatus(201);
        $noteId = $response->json('data.id');

        // 3. Admin creates Question Bank for Chapter
        $response = $this->actingAs($adminUser, 'sanctum')->postJson('/api/v2/admin/question-banks', [
            'chapter_id' => $chapterId,
            'title' => 'Chapter 1 Quiz',
            'passing_marks' => 1
        ]);
        $response->assertStatus(201);
        $chapterQuizId = $response->json('data.id');

        // 4. Admin creates Question Bank for Topic Note
        $response = $this->actingAs($adminUser, 'sanctum')->postJson('/api/v2/admin/question-banks', [
            'chapter_id' => $chapterId,
            'topic_note_id' => $noteId,
            'title' => 'Note 1 Quiz',
            'passing_marks' => 1
        ]);
        $response->assertStatus(201);
        $noteQuizId = $response->json('data.id');

        // Validate mismatch Topic Note rejected
        $fakeChapter = Topic::create(['class_id' => $class->id, 'subject_id' => $subject->id, 'title' => 'Fake']);
        $response = $this->actingAs($adminUser, 'sanctum')->postJson('/api/v2/admin/question-banks', [
            'chapter_id' => $fakeChapter->id,
            'topic_note_id' => $noteId,
            'title' => 'Invalid Quiz',
            'passing_marks' => 1
        ]);
        $response->assertStatus(422); // Note doesn't belong to this chapter

        // 5. Admin creates Questions
        // Easy for chapter
        $this->actingAs($adminUser, 'sanctum')->postJson('/api/v2/admin/questions', [
            'question_bank_id' => $chapterQuizId,
            'question' => 'Easy 1+1?',
            'option_a' => '1', 'option_b' => '2', 'option_c' => '3', 'option_d' => '4',
            'correct_option' => 'b',
            'difficulty_level' => 'Easy'
        ])->assertStatus(201);

        // Hard for topic note
        $this->actingAs($adminUser, 'sanctum')->postJson('/api/v2/admin/questions', [
            'question_bank_id' => $noteQuizId,
            'question' => 'Hard 2*2?',
            'option_a' => '1', 'option_b' => '2', 'option_c' => '4', 'option_d' => '5',
            'correct_option' => 'c',
            'difficulty_level' => 'Hard'
        ])->assertStatus(201);

        // 6. Test Generation (Student fetches chapter test with Mixed difficulty)
        $response = $this->actingAs($studentUser, 'sanctum')->getJson("/api/v2/tests/chapters/{$chapterId}/generate?difficulty=Mixed");
        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.total_questions')); // Should pull both the chapter direct question and the topic note question

        // Student fetches chapter test with Hard difficulty
        $response = $this->actingAs($studentUser, 'sanctum')->getJson("/api/v2/tests/chapters/{$chapterId}/generate?difficulty=Hard");
        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.total_questions'));

        // 7. Student fetches Topic Note test directly
        $response = $this->actingAs($studentUser, 'sanctum')->getJson("/api/v2/tests/topic-notes/{$noteId}/generate");
        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.total_questions'));

        $this->assertTrue(true);
    }
}
