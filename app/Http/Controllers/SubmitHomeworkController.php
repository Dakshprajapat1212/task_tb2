<?php

namespace App\Http\Controllers;

use App\Models\AssignHomework;
use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\SubmitHomework;
use App\Services\StudentStreakService;
use Illuminate\Http\Request;

class SubmitHomeworkController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET SUBMISSIONS
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        if (auth()->user()->role_id == 3) {

            $submissions = SubmitHomework::with([
                'assignHomework.subject',
                'assignHomework.class',
                'student.user'
            ])->get();

            return response()->json([
                'success' => true,
                'message' => 'All homework submissions fetched successfully',
                'data' => $submissions
            ], 200);
        }

        if (auth()->user()->role_id == 2) {

            $faculty = Faculty::where('user_id', auth()->id())->first();

            if (!$faculty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faculty profile not found'
                ], 404);
            }

            $classIds = ClassModel::forFaculty($faculty->id)->pluck('id');
            $homeworkIds = AssignHomework::whereIn('class_id', $classIds)->pluck('id');

            $submissions = SubmitHomework::whereIn('assign_homework_id', $homeworkIds)
                ->with([
                    'assignHomework.subject',
                    'assignHomework.class',
                    'student.user'
                ])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Faculty homework submissions fetched successfully',
                'data' => $submissions
            ], 200);
        }

        $student = Student::where('user_id', auth()->id())->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found'
            ], 404);
        }

        $submissions = SubmitHomework::where('student_id', $student->id)
            ->with([
                'assignHomework.subject',
                'assignHomework.class',
                'student.user'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'My homework submissions fetched successfully',
            'data' => $submissions
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE SUBMISSION
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        if (auth()->user()->role_id != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Only students can submit homework'
            ], 403);
        }

        $request->validate([
            'assign_homework_id' => 'required|exists:assign_homeworks,id',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,txt,zip|max:10240',
            'file_url' => 'nullable|url',
            'student_comment' => 'nullable|string|max:1000'
        ]);

        if (!$request->hasFile('file') && !$request->filled('file_url')) {
            return response()->json([
                'success' => false,
                'message' => 'Either file or file_url is required'
            ], 422);
        }

        $student = Student::where('user_id', auth()->id())->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete enrollment approval first'
            ], 403);
        }

        $homework = AssignHomework::find($request->assign_homework_id);

        if (!$this->studentCanAccessHomework($homework)) {
            return response()->json([
                'success' => false,
                'message' => 'You can submit only for homework from approved enrolled classes'
            ], 403);
        }

        $existingSubmission = SubmitHomework::where('assign_homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingSubmission) {
            return response()->json([
                'success' => false,
                'message' => 'Homework already submitted',
                'data' => $existingSubmission
            ], 409);
        }

        $filePath = $request->filled('file_url')
            ? $request->file_url
            : $request->file('file')->store('homework-submissions', 'public');

        $submission = SubmitHomework::create([
            'assign_homework_id' => $homework->id,
            'student_id'         => $student->id,
            'file'               => $filePath,
            'status'             => 'pending',
            'remarks'            => null,
            'student_comment'    => $request->student_comment
        ]);

        // Update streak — submitting homework counts as study activity
        (new StudentStreakService())->updateStreak($student);

        $submission->load([
            'assignHomework.subject',
            'assignHomework.class',
            'student.user'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Homework submitted successfully',
            'data'    => $submission
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | REVIEW SUBMISSION
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        if (!in_array(auth()->user()->role_id, [2, 3])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $submission = SubmitHomework::with('assignHomework')->find($id);

        if (!$submission) {
            return response()->json([
                'success' => false,
                'message' => 'Homework submission not found'
            ], 404);
        }

        if (auth()->user()->role_id == 2 && !$this->facultyCanReviewSubmission($submission)) {
            return response()->json([
                'success' => false,
                'message' => 'You can review only submissions from your own classes'
            ], 403);
        }

        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected',
            'remarks' => 'nullable|string|max:1000',
            'graded_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'graded_file_url' => 'nullable|string'
        ]);

        $gradedFilePath = $submission->graded_file;
        if ($request->hasFile('graded_file')) {
            $gradedFilePath = $request->file('graded_file')->store('graded-submissions', 'public');
        } elseif ($request->filled('graded_file_url')) {
            $gradedFilePath = $request->graded_file_url;
        }

        // Capture status BEFORE the update to detect the approved transition
        $previousStatus = $submission->status;

        $submission->update([
            'status'      => $request->status,
            'remarks'     => $request->remarks,
            'graded_file' => $gradedFilePath
        ]);

        // Award XP only on the FIRST transition to 'approved' — prevents double-awarding
        if ($request->status === 'approved' && $previousStatus !== 'approved') {
            $xpToAward = $submission->assignHomework->xp ?? 50;
            (new \App\Services\XpService())->awardXp($submission->student, $xpToAward, 'homework', 'Approved homework submission', $submission->id);
            (new \App\Services\BadgeService())->checkAndAwardBadges($submission->student);
        }

        $submission->load([
            'assignHomework.subject',
            'assignHomework.class',
            'student.user'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Homework submission reviewed successfully',
            'data'    => $submission
        ], 200);
    }

    private function studentCanAccessHomework(AssignHomework $homework)
    {
        return Enrollment::where('user_id', auth()->id())
            ->where('class_id', $homework->class_id)
            ->where('status', 'approved')
            ->exists();
    }

    private function facultyCanReviewSubmission(SubmitHomework $submission)
    {
        $faculty = Faculty::where('user_id', auth()->id())->first();

        if (!$faculty || !$submission->assignHomework) {
            return false;
        }

        return ClassModel::forFaculty($faculty->id)->where('id', $submission->assignHomework->class_id)
            ->exists();
    }
}
