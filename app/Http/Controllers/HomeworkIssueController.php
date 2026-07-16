<?php

namespace App\Http\Controllers;

use App\Models\AssignHomework;
use App\Models\Enrollment;
use App\Models\HomeworkIssue;
use App\Models\Student;
use Illuminate\Http\Request;

class HomeworkIssueController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'assign_homework_id' => 'required|exists:assign_homeworks,id',
            'issue_type' => 'required|string|in:Cropped Image,Incorrect Question,Missing Data,Other',
            'description' => 'required|string|max:1000'
        ]);

        $student = Student::where('user_id', auth()->id())->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found. Please complete admission.'
            ], 403);
        }

        $homework = AssignHomework::find($request->assign_homework_id);

        $enrolled = Enrollment::where('user_id', auth()->id())
            ->where('class_id', $homework->class_id)
            ->where('status', 'approved')
            ->exists();

        if (!$enrolled) {
            return response()->json([
                'success' => false,
                'message' => 'You can report issues only for assigned homeworks in approved classes'
            ], 403);
        }

        $existing = HomeworkIssue::where('assign_homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reported an issue for this assignment',
                'data' => $existing
            ], 409);
        }

        $issue = HomeworkIssue::create([
            'assign_homework_id' => $homework->id,
            'student_id' => $student->id,
            'issue_type' => $request->issue_type,
            'description' => $request->description,
            'status' => 'Under Review'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Issue flagged successfully',
            'data' => $issue
        ], 201);
    }
}
