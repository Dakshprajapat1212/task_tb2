<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\LiveAttendance;
use App\Services\StudentStreakService;
use Illuminate\Http\Request;

class LiveAttendanceController extends Controller
{
    /**
     * POST /api/student/live-attendance/join
     * Records that a student has joined a virtual classroom.
     */
    public function join(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $student = Student::where('user_id', auth()->id())->firstOrFail();
        $today = now()->toDateString();

        $attendance = LiveAttendance::firstOrCreate([
            'student_id' => $student->id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'class_date' => $today,
        ], [
            'joined_at' => now(),
            'duration_seconds' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Joined virtual classroom successfully',
            'data' => $attendance
        ], 200);
    }

    /**
     * POST /api/student/live-attendance/complete
     * Marks attendance as completed (30 seconds elapsed) and awards +20 XP.
     */
    public function complete(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $student = Student::where('user_id', auth()->id())->firstOrFail();
        $today = now()->toDateString();

        $attendance = LiveAttendance::where([
            'student_id' => $student->id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'class_date' => $today,
        ])->first();

        if (!$attendance) {
            // Fallback: create record if join wasn't logged earlier
            $attendance = LiveAttendance::create([
                'student_id' => $student->id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'class_date' => $today,
                'joined_at' => now(),
                'duration_seconds' => 0,
            ]);
        }

        $xpAwarded = 0;

        // Only award XP and set completed_at on the FIRST completion
        if (is_null($attendance->completed_at)) {
            $attendance->completed_at = now();
            $attendance->duration_seconds = 30;
            $attendance->save();

            // Award +20 XP with itemized audit log
            (new \App\Services\XpService())->awardXp($student, 20, 'live_class', 'Attended live class session', $attendance->id);
            $xpAwarded = 20;

            // Trigger Streak increment as this counts as study activity
            $streakService = new StudentStreakService();
            $streakService->updateStreak($student);

            // Evaluate and award any eligible badges
            (new \App\Services\BadgeService())->checkAndAwardBadges($student);
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance verified and marked complete',
            'data' => [
                'attendance' => $attendance,
                'xp_awarded' => $xpAwarded
            ]
        ], 200);
    }
}
