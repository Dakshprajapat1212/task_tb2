<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\MasRoleController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\RecordingController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\AssignHomeworkController;
use App\Http\Controllers\SubmitHomeworkController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\HomeworkIssueController;

/*
|--------------------------------------------------------------------------
| PUBLIC AUTH ROUTES
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (SESSION AUTH)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth.session.api'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | COMMON AUTH ROUTES
    |--------------------------------------------------------------------------
    */

    Route::get('/me-test', function(\Illuminate\Http\Request $request) { return response()->json(['auth_header' => $request->header('Authorization')]); }); Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'user']);
    Route::get('/enrollments/{id}', [EnrollmentController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | STUDENT: ENROLLMENT REQUEST FLOW
    | - Student can request enrollment
    | - Student can view their enrollment request by id
    |--------------------------------------------------------------------------
    */

    Route::middleware(['isStudent'])->group(function () {

        // Student can fetch notes for a particular class (you already created classNotes)
        Route::get('/classes/{id}/notes', [NoteController::class, 'classNotes']);

        Route::get('/my-enrollments',    [EnrollmentController::class, 'myEnrollments']);
        Route::post('/enrollments',      [EnrollmentController::class, 'store']);
        Route::get('/available-classes', [ClassController::class, 'index']);
    });

    /*
    |--------------------------------------------------------------------------
    | STUDENT: AFTER ACCESS GRANTED (hasAccess)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['isStudent', 'hasAccess'])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | CLASSES (Student Dashboard)
        |--------------------------------------------------------------------------
        */

        // Student gets only enrolled+approved classes (your myClasses() uses enrollments)
        Route::get('/my-classes', [ClassController::class, 'myClasses']);

        // Optional: student can open a single class details page
        Route::get('/classes/{id}', [ClassController::class, 'show']);

        /*
        |--------------------------------------------------------------------------
        | NOTES (Student)
        |--------------------------------------------------------------------------
        */

        // Student sees notes for ALL their approved classes (your NoteController@index)
        Route::get('/notes',      [NoteController::class, 'index']);
        Route::get('/notes/{id}', [NoteController::class, 'show']);

        /*
        |--------------------------------------------------------------------------
        | RECORDINGS (Student)  ✅ FIXED (no conflict now)
        |--------------------------------------------------------------------------
        | Student must choose a class first, then fetch recordings of that class only.
        */

        Route::get(
            '/classes/{class_id}/recordings',
            [RecordingController::class, 'studentClassRecordings']
        );

        Route::get(
            '/student/recordings/{id}',
            [RecordingController::class, 'studentShow']
        );

        /*
        |--------------------------------------------------------------------------
        | ASSIGN HOMEWORKS (Student)
        |--------------------------------------------------------------------------
        */

        Route::get('/assign-homeworks',      [AssignHomeworkController::class, 'index']);
        Route::get('/assign-homeworks/{id}', [AssignHomeworkController::class, 'show']);

        /*
        |--------------------------------------------------------------------------
        | SUBMIT HOMEWORKS (Student)
        |--------------------------------------------------------------------------
        */

        Route::get('/submit-homeworks', [SubmitHomeworkController::class, 'index']);
        Route::post('/submit-homeworks',[SubmitHomeworkController::class, 'store']);
        Route::post('/homework-issues', [HomeworkIssueController::class, 'store']);

        /*
        |--------------------------------------------------------------------------
        | SUBJECTS (Student)
        |--------------------------------------------------------------------------
        */

        Route::get('/subjects',      [SubjectController::class, 'index']);
        Route::get('/subjects/{id}', [SubjectController::class, 'show']);

        /*
        |--------------------------------------------------------------------------
        | LIBRARY (Student)
        |--------------------------------------------------------------------------
        */

        Route::get('/library/classes', [LibraryController::class, 'classes']);
        Route::get('/library/classes/{class}/subjects', [LibraryController::class, 'subjects']);
        Route::get('/library/classes/{class}/subjects/{subject}/chapters', [LibraryController::class, 'chapters']);
        Route::get('/library/chapters/{chapter}/notes', [LibraryController::class, 'notes']);
        Route::get('/library/notes/{note}', [LibraryController::class, 'note']);
        Route::post('/library/notes/{note}/complete', [LibraryController::class, 'completeNote']);
        Route::get('/library/chapters/{chapter}/progress', [LibraryController::class, 'progress']);
        Route::get('/library/chapters/{chapter}/quiz', [LibraryController::class, 'quiz']);
        Route::post('/library/chapters/{chapter}/submit', [LibraryController::class, 'submitChapterQuiz']);
        Route::get('/library/chapters/{chapter}/result', [LibraryController::class, 'chapterQuizResult']);
        Route::get('/library/chapters/{chapter}/flashcards', [LibraryController::class, 'flashcards']);
        Route::get('/library/modules/{note}/quiz', [LibraryController::class, 'moduleQuiz']);
        Route::post('/library/modules/{note}/submit', [LibraryController::class, 'submitModuleQuiz']);
        Route::get('/library/modules/{note}/result', [LibraryController::class, 'moduleQuizResult']);
        Route::get('/library/modules/{note}/flashcards', [LibraryController::class, 'moduleFlashcards']);

        // Doubts (Student)
        Route::post('/doubts/search', [\App\Http\Controllers\DoubtController::class, 'search']);
        Route::post('/doubts', [\App\Http\Controllers\DoubtController::class, 'store']);
        Route::get('/library/my-doubts', [LibraryController::class, 'myDoubts']);

        // V2 New Business Terminology Routes
        Route::get('/v2/library/classes/{class}/subjects/{subject}/chapters', [LibraryController::class, 'v2Chapters']);
        Route::get('/v2/library/chapters/{chapter}/topic-notes', [LibraryController::class, 'v2TopicNotes']);
        Route::get('/v2/library/chapters/{chapter}/question-bank', [LibraryController::class, 'v2ChapterQuestionBank']);
        Route::get('/v2/library/topic-notes/{topic_note}/question-bank', [LibraryController::class, 'v2TopicNoteQuestionBank']);
    });

    /*
    |--------------------------------------------------------------------------
    | FACULTY ROUTES
    |--------------------------------------------------------------------------
    */

    Route::middleware(['isFaculty'])->group(function () {


        //check my classses 

      Route::get(
    '/faculty/my-classes',
    [ClassController::class, 'facultyClasses']
);
        /*
        |--------------------------------------------------------------------------
        | RECORDINGS (Faculty) ✅ FIXED (no conflict now)
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/faculty/classes/{class_id}/recordings',
            [RecordingController::class, 'facultyClassRecordings']
        );

        Route::post(
            '/faculty/classes/{class_id}/recordings',
            [RecordingController::class, 'facultyStoreInClass']
        );

        Route::get(
            '/faculty/recordings/{id}',
            [RecordingController::class, 'facultyShow'] // make sure this method exists
        );

        Route::put(
            '/faculty/recordings/{id}',
            [RecordingController::class, 'facultyUpdate']
        );

        Route::delete(
            '/faculty/recordings/{id}',
            [RecordingController::class, 'facultyDestroy']
        );

        /*
        |--------------------------------------------------------------------------
        | NOTES (Faculty)
        |--------------------------------------------------------------------------
        */

        Route::get('/notes',        [NoteController::class, 'index']);
        Route::get('/notes/{id}',   [NoteController::class, 'show']);
        Route::post('/notes',       [NoteController::class, 'store']);
        Route::put('/notes/{id}',   [NoteController::class, 'update']);
        Route::delete('/notes/{id}',[NoteController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | ASSIGN HOMEWORKS (Faculty)
        |--------------------------------------------------------------------------
        */

        Route::get('/assign-homeworks',       [AssignHomeworkController::class, 'index']);
        Route::get('/assign-homeworks/{id}',  [AssignHomeworkController::class, 'show']);
        Route::post('/assign-homeworks',      [AssignHomeworkController::class, 'store']);
        Route::put('/assign-homeworks/{id}',  [AssignHomeworkController::class, 'update']);
        Route::delete('/assign-homeworks/{id}',[AssignHomeworkController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | SUBMIT HOMEWORKS (Faculty)
        |--------------------------------------------------------------------------
        */

        Route::get('/submit-homeworks',      [SubmitHomeworkController::class, 'index']);
        Route::put('/submit-homeworks/{id}', [SubmitHomeworkController::class, 'update']);

        /*
        |--------------------------------------------------------------------------
        | DOUBTS (Faculty)
        |--------------------------------------------------------------------------
        */
        Route::get('/faculty/doubts', [\App\Http\Controllers\DoubtController::class, 'facultyIndex']);
        Route::put('/faculty/doubts/{id}', [\App\Http\Controllers\DoubtController::class, 'facultyAnswer']);
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */

    Route::middleware(['isAdmin'])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | RECORDINGS (Admin) ✅ FIXED (no conflict now)
        |--------------------------------------------------------------------------
        */

        Route::get('/admin/recordings',        [RecordingController::class, 'adminIndex']);
        Route::get('/admin/recordings/{id}',   [RecordingController::class, 'adminShow']);
        Route::post('/admin/recordings',       [RecordingController::class, 'adminStore']);
        Route::put('/admin/recordings/{id}',   [RecordingController::class, 'adminUpdate']);
        Route::delete('/admin/recordings/{id}',[RecordingController::class, 'adminDestroy']);

        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        */

        Route::get('/users',        [UserController::class, 'index']);
        Route::post('/users',       [UserController::class, 'store']);
        Route::get('/users/{id}',   [UserController::class, 'show']);
        Route::put('/users/{id}',   [UserController::class, 'update']);
        Route::delete('/users/{id}',[UserController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | STUDENTS
        |--------------------------------------------------------------------------
        */

        Route::get('/students',         [StudentController::class, 'index']);
        Route::post('/students',        [StudentController::class, 'store']);
        Route::put('/students/{id}',    [StudentController::class, 'update']);
        Route::delete('/students/{id}', [StudentController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | ENROLLMENTS (Admin approves/rejects)
        |--------------------------------------------------------------------------
        */

        Route::get('/enrollments',          [EnrollmentController::class, 'index']);
        Route::put('/enrollments/{id}',     [EnrollmentController::class, 'update']);
        Route::delete('/enrollments/{id}',  [EnrollmentController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | FACULTIES
        |--------------------------------------------------------------------------
        */

        Route::get('/faculties',         [FacultyController::class, 'index']);
        Route::post('/faculties',        [FacultyController::class, 'store']);
        Route::get('/faculties/{id}',    [FacultyController::class, 'show']);
        Route::put('/faculties/{id}',    [FacultyController::class, 'update']);
        Route::delete('/faculties/{id}', [FacultyController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | SUBJECTS
        |--------------------------------------------------------------------------
        */

        Route::get('/subjects',         [SubjectController::class, 'index']);
        Route::post('/subjects',        [SubjectController::class, 'store']);
        Route::get('/subjects/{id}',    [SubjectController::class, 'show']);
        Route::put('/subjects/{id}',    [SubjectController::class, 'update']);
        Route::delete('/subjects/{id}', [SubjectController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | CLASSES
        |--------------------------------------------------------------------------
        */

        Route::get('/classes',         [ClassController::class, 'index']);
        Route::post('/classes',        [ClassController::class, 'store']);
        Route::get('/classes/{id}',    [ClassController::class, 'show']);
        Route::put('/classes/{id}',    [ClassController::class, 'update']);
        Route::delete('/classes/{id}', [ClassController::class, 'destroy']);
        Route::post('/classes/{id}/assign-subject', [ClassController::class, 'assignSubject']);

        /*
        |--------------------------------------------------------------------------
        | NOTES (Admin)
        |--------------------------------------------------------------------------
        */

        Route::get('/notes',          [NoteController::class, 'index']);
        Route::get('/notes/{id}',     [NoteController::class, 'show']);
        Route::post('/notes',         [NoteController::class, 'store']);
        Route::put('/notes/{id}',     [NoteController::class, 'update']);
        Route::delete('/notes/{id}',  [NoteController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | ASSIGN HOMEWORKS (Admin)
        |--------------------------------------------------------------------------
        */

        Route::get('/assign-homeworks',        [AssignHomeworkController::class, 'index']);
        Route::get('/assign-homeworks/{id}',   [AssignHomeworkController::class, 'show']);
        Route::post('/assign-homeworks',       [AssignHomeworkController::class, 'store']);
        Route::put('/assign-homeworks/{id}',   [AssignHomeworkController::class, 'update']);
        Route::delete('/assign-homeworks/{id}',[AssignHomeworkController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | SUBMIT HOMEWORKS (Admin can review/update if you want)
        |--------------------------------------------------------------------------
        */

        Route::get('/submit-homeworks',      [SubmitHomeworkController::class, 'index']);
        Route::put('/submit-homeworks/{id}', [SubmitHomeworkController::class, 'update']);

        /*
        |--------------------------------------------------------------------------
        | MAS ROLES
        |--------------------------------------------------------------------------
        */

        Route::get('/mas-roles',         [MasRoleController::class, 'index']);
        Route::post('/mas-roles',        [MasRoleController::class, 'store']);
        Route::get('/mas-roles/{id}',    [MasRoleController::class, 'show']);
        Route::put('/mas-roles/{id}',    [MasRoleController::class, 'update']);
        Route::delete('/mas-roles/{id}', [MasRoleController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | DOUBTS (Admin)
        |--------------------------------------------------------------------------
        */
        Route::get('/admin/doubts', [\App\Http\Controllers\DoubtController::class, 'adminIndex']);
        Route::post('/admin/doubts/{id}/answer', [\App\Http\Controllers\DoubtController::class, 'adminAnswer']);
    });

    /*
    |--------------------------------------------------------------------------
    | TASKS (Keep as is)
    |--------------------------------------------------------------------------
    */

    Route::apiResource('tasks', TaskController::class);

    /*
    |--------------------------------------------------------------------------
    | SHARED LMS ROUTES
    |--------------------------------------------------------------------------
    | These routes keep the same API URLs available to all authenticated roles.
    | Controller methods enforce student/faculty/admin permissions.
    |--------------------------------------------------------------------------
    */

    Route::get('/classes/{id}', [ClassController::class, 'show']);

    Route::get('/subjects',      [SubjectController::class, 'index']);
    Route::get('/subjects/{id}', [SubjectController::class, 'show']);

    Route::get('/notes',          [NoteController::class, 'index']);
    Route::get('/notes/{id}',     [NoteController::class, 'show']);
    Route::post('/notes',         [NoteController::class, 'store']);
    Route::put('/notes/{id}',     [NoteController::class, 'update']);
    Route::delete('/notes/{id}',  [NoteController::class, 'destroy']);

    Route::get('/assign-homeworks',        [AssignHomeworkController::class, 'index']);
    Route::get('/assign-homeworks/{id}',   [AssignHomeworkController::class, 'show']);
    Route::post('/assign-homeworks',       [AssignHomeworkController::class, 'store']);
    Route::put('/assign-homeworks/{id}',   [AssignHomeworkController::class, 'update']);
    Route::delete('/assign-homeworks/{id}',[AssignHomeworkController::class, 'destroy']);

    Route::get('/submit-homeworks',      [SubmitHomeworkController::class, 'index']);
    Route::post('/submit-homeworks',     [SubmitHomeworkController::class, 'store']);
    Route::put('/submit-homeworks/{id}', [SubmitHomeworkController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | V2 SHARED ADMIN/TEACHER ROUTES (Enforced by controllers)
    |--------------------------------------------------------------------------
    */
    Route::apiResource('v2/admin/chapters', \App\Http\Controllers\V2AdminChapterController::class);
    Route::apiResource('v2/admin/topic-notes', \App\Http\Controllers\V2AdminTopicNoteController::class);
    Route::apiResource('v2/admin/questions', \App\Http\Controllers\V2AdminQuestionController::class);

});
