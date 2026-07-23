<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentBadge;
use App\Models\StudentNoteProgress;
use App\Models\SubmitHomework;
use App\Models\QuizAttempt;
use App\Models\LiveAttendance;
use App\Models\Enrollment;

class BadgeService
{
    /**
     * Evaluates all badge conditions for a student and persists any newly unlocked badges.
     * Returns an array of newly awarded badge titles.
     */
    public function checkAndAwardBadges(Student $student): array
    {
        $newlyUnlocked = [];

        // Calculate metrics
        $notesCompleted = StudentNoteProgress::where('student_id', $student->id)->count();

        $approvedSubmissions = SubmitHomework::where('student_id', $student->id)
            ->where('status', 'approved')
            ->count();

        $quizAttempts = QuizAttempt::where('student_id', $student->id)->get();
        $totalQuizzes = $quizAttempts->count();
        $quizScoreSum = $quizAttempts->sum('score_percentage');
        $totalEvaluated = $totalQuizzes + $approvedSubmissions;
        $avgMark = $totalEvaluated > 0
            ? round(($quizScoreSum + ($approvedSubmissions * 90)) / max(1, $totalEvaluated), 1)
            : 85.0;

        $studentClassIds = Enrollment::where('user_id', $student->user_id)
            ->where('status', 'approved')
            ->pluck('class_id');
        $totalRecordings = \App\Models\Recording::whereIn('class_id', $studentClassIds)->count();
        $attendedCount = LiveAttendance::where('student_id', $student->id)
            ->whereIn('class_id', $studentClassIds)
            ->whereNotNull('completed_at')
            ->count();
        $attendancePct = $totalRecordings > 0
            ? min(100, round(($attendedCount / $totalRecordings) * 100))
            : 95;

        $streakDays = $student->streak_days;

        // Badge rules mapping: badge_id => [title, eligible condition]
        $rules = [
            'notes-master'     => ['title' => 'Notes Master',     'eligible' => $notesCompleted >= 1],
            'consistency-king' => ['title' => 'Consistency King', 'eligible' => $streakDays >= 5],
            'quiz-genius'      => ['title' => 'Quiz Genius',      'eligible' => $avgMark >= 88],
            'attendance-hero'  => ['title' => 'Attendance Hero',  'eligible' => $attendancePct >= 85],
        ];

        foreach ($rules as $badgeId => $rule) {
            if ($rule['eligible']) {
                $alreadyUnlocked = StudentBadge::where('student_id', $student->id)
                    ->where('badge_id', $badgeId)
                    ->exists();

                if (!$alreadyUnlocked) {
                    StudentBadge::create([
                        'student_id'  => $student->id,
                        'badge_id'    => $badgeId,
                        'title'       => $rule['title'],
                        'unlocked_at' => now(),
                    ]);
                    $newlyUnlocked[] = $rule['title'];
                }
            }
        }

        return $newlyUnlocked;
    }
}
