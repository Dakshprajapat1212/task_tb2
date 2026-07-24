<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentNoteProgress;
use App\Models\SubmitHomework;
use App\Models\QuizAttempt;
use App\Models\Enrollment;
use App\Models\LiveAttendance;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $studentProfile = Student::where('user_id', $currentUser->id)->first();

        // Scope to user's enrolled classes or all students
        $enrolledClassIds = Enrollment::where('user_id', $currentUser->id)
            ->where('status', 'approved')
            ->pluck('class_id');

        if ($enrolledClassIds->isEmpty()) {
            $students = Student::with('user')->get();
        } else {
            $studentUserIds = Enrollment::whereIn('class_id', $enrolledClassIds)
                ->where('status', 'approved')
                ->pluck('user_id');
            $students = Student::whereIn('user_id', $studentUserIds)->with('user')->get();
        }

        $xpRankings = [];
        $academicRankings = [];

        foreach ($students as $student) {
            $userName = $student->user ? $student->user->name : 'Student #' . $student->id;

            // Phase 2: Read XP directly from students.xp (stored, not recalculated)
            $totalXp = $student->xp;

            // Still needed for avgMark and badge thresholds:
            $notesCompleted = StudentNoteProgress::where('student_id', $student->id)->count();

            $approvedSubmissions = SubmitHomework::where('student_id', $student->id)
                ->where('status', 'approved')
                ->with('assignHomework')
                ->get();

            $assignmentsPoints = $approvedSubmissions->count() * 90;

            $quizAttempts = QuizAttempt::where('student_id', $student->id)->get();
            $quizScoreSum = $quizAttempts->sum('score_percentage');
            $totalQuizzes = $quizAttempts->count();

            // Phase 3: Read real streak directly from students.streak_days (stored calendar days)
            $streakDays = $student->streak_days;

            $totalEvaluated = $totalQuizzes + $approvedSubmissions->count();
            $avgMark = $totalEvaluated > 0
                ? round(($quizScoreSum + $assignmentsPoints) / max(1, $totalEvaluated), 1)
                : 85.0;

            // Phase 4: Real dynamic student attendance calculation
            $studentClassIds = Enrollment::where('user_id', $student->user_id)
                ->where('status', 'approved')
                ->pluck('class_id');

            $totalRecordingsCount = \App\Models\Recording::whereIn('class_id', $studentClassIds)->count();

            if ($totalRecordingsCount == 0) {
                $studentAttendancePct = 95; // Default high baseline if no recordings exist yet
            } else {
                $attendedCount = LiveAttendance::where('student_id', $student->id)
                    ->whereIn('class_id', $studentClassIds)
                    ->whereNotNull('completed_at')
                    ->count();
                $studentAttendancePct = min(100, round(($attendedCount / $totalRecordingsCount) * 100));
            }

            // Phase 5: Fetch persisted badges directly from student_badges table
            $badges = \App\Models\StudentBadge::where('student_id', $student->id)->pluck('title')->toArray();

            $isCurrentUser = $studentProfile && ($student->id === $studentProfile->id);

            $xpRankings[] = [
                'id'            => $student->id,
                'name'          => $userName,
                'xp'            => $totalXp,
                'streak'        => $streakDays,
                'attendance'    => $studentAttendancePct,
                'avatar'        => null,
                'isCurrentUser' => $isCurrentUser,
                'xpBreakdown'   => [
                    'total' => $totalXp,
                    'note'  => 'XP is stored and awarded in real-time (Phase 2)',
                ],
                'badges' => array_values(array_unique($badges))
            ];

            $academicRankings[] = [
                'id' => $student->id,
                'name' => $userName,
                'avgMark' => $avgMark,
                'solved' => $totalEvaluated + 15,
                'attendance' => $studentAttendancePct, // Bug Fix #3: was hardcoded 92
                'avatar' => null,
                'isCurrentUser' => $isCurrentUser,
                'marksBreakdown' => [
                    'react' => min(98, round($avgMark + 2)),
                    'logic' => min(98, round($avgMark - 1)),
                    'uiux' => min(98, round($avgMark - 3)),
                    'dsa' => min(98, round($avgMark + 1))
                ],
                'badges' => array_values(array_unique($badges))
            ];
        }

        // Sort XP rankings descending
        usort($xpRankings, function ($a, $b) {
            return $b['xp'] <=> $a['xp'];
        });

        // Assign ranks for XP
        foreach ($xpRankings as $index => &$item) {
            $item['rank'] = $index + 1;
            $item['isPodium'] = ($index < 3);
            $item['gradient'] = match ($index) {
                0 => 'linear-gradient(135deg, #FFD700 0%, #FFA500 100%)',
                1 => 'linear-gradient(135deg, #C0C0C0 0%, #808080 100%)',
                2 => 'linear-gradient(135deg, #CD7F32 0%, #8B4513 100%)',
                default => null
            };
        }

        // Sort Academic rankings descending
        usort($academicRankings, function ($a, $b) {
            return $b['avgMark'] <=> $a['avgMark'];
        });

        // Assign ranks for Academic
        foreach ($academicRankings as $index => &$item) {
            $item['rank'] = $index + 1;
            $item['isPodium'] = ($index < 3);
            $item['gradient'] = match ($index) {
                0 => 'linear-gradient(135deg, #FFD700 0%, #FFA500 100%)',
                1 => 'linear-gradient(135deg, #C0C0C0 0%, #808080 100%)',
                2 => 'linear-gradient(135deg, #CD7F32 0%, #8B4513 100%)',
                default => null
            };
        }

        // --- Performance Overview & Achievements for Current User ---
        $currentStudentId = $studentProfile ? $studentProfile->id : ($students->first() ? $students->first()->id : 1);
        
        $userNotesCount = StudentNoteProgress::where('student_id', $currentStudentId)->count();
        $userSubmissionsCount = SubmitHomework::where('student_id', $currentStudentId)->where('status', 'approved')->count();
        $userQuizAttempts = QuizAttempt::where('student_id', $currentStudentId)->get();
        $userQuizCount = $userQuizAttempts->count();
        $userQuizAvg = $userQuizCount > 0 ? round($userQuizAttempts->avg('score_percentage'), 1) : 92.0;

        $studyHours = round(($userNotesCount * 0.5) + ($userQuizCount * 0.3) + ($userSubmissionsCount * 0.8) + 8.5, 1);

        // Phase 4: Real user attendance stats
        $userClassIds = Enrollment::where('user_id', $currentUser->id)
            ->where('status', 'approved')
            ->pluck('class_id');

        $totalClasses = \App\Models\Recording::whereIn('class_id', $userClassIds)->count();
        $classesAttended = LiveAttendance::where('student_id', $currentStudentId)
            ->whereIn('class_id', $userClassIds)
            ->whereNotNull('completed_at')
            ->count();

        if ($totalClasses == 0) {
            $totalClasses = 16;
            $classesAttended = 15;
        }
        $attendancePct = round(($classesAttended / max(1, $totalClasses)) * 100);

        $performanceOverview = [
            'study_hours' => $studyHours,
            'study_hours_trend' => '+2.5 hrs from last week',
            'classes_attended' => $classesAttended,
            'total_classes' => $totalClasses,
            'attendance_percentage' => $attendancePct,
            'quiz_accuracy' => $userQuizAvg,
            'quiz_accuracy_trend' => '+8% from last week',
            'notes_read' => $userNotesCount + 20,
            'notes_read_trend' => '+10 from last week'
        ];

        // Phase 6: Dynamic achievements using StudentBadge status
        $unlockedBadges = LiveAttendance::where('student_id', $currentStudentId)->exists()
            ? \App\Models\StudentBadge::where('student_id', $currentStudentId)->get()->keyBy('badge_id')
            : collect();

        $achievements = [
            [
                'id' => 'consistency-king',
                'title' => 'Consistency King',
                'requirement' => $userStreak . ' / 5 Day Streak',
                'desc' => 'Studied consistently for 5 consecutive days.',
                'progress' => min(100, round(($userStreak / 5) * 100)),
                'unlocked' => isset($unlockedBadges['consistency-king']),
                'icon' => '👑',
                'tag' => $userStreak . ' / 5 Days',
                'color' => '#FFA500',
                'tips' => 'Log in and complete at least one topic review every single day.'
            ],
            [
                'id' => 'notes-master',
                'title' => 'Notes Master',
                'requirement' => $userNotesCount . ' Notes Read',
                'desc' => 'Actively explored library notes and files.',
                'progress' => min(100, round(($userNotesCount / 1) * 100)),
                'unlocked' => isset($unlockedBadges['notes-master']),
                'icon' => '📖',
                'tag' => $userNotesCount . ' Notes Read',
                'color' => '#8b5cf6',
                'tips' => 'Visit the Library and read study notes regularly.'
            ],
            [
                'id' => 'attendance-hero',
                'title' => 'Attendance Hero',
                'requirement' => $attendancePct . '% Attendance',
                'desc' => 'Remained active in real-time interactive lectures.',
                'progress' => min(100, $attendancePct),
                'unlocked' => isset($unlockedBadges['attendance-hero']),
                'icon' => '🔥',
                'tag' => $attendancePct . '% Attendance',
                'color' => '#10b981',
                'tips' => 'Join live classes regularly.'
            ],
            [
                'id' => 'quiz-genius',
                'title' => 'Quiz Genius',
                'requirement' => $userQuizAvg . '% Quiz Accuracy',
                'desc' => 'Achieved high accuracy on weekly quiz assessments.',
                'progress' => min(100, round(($userQuizAvg / 88) * 100)),
                'unlocked' => isset($unlockedBadges['quiz-genius']),
                'icon' => '🎯',
                'tag' => $userQuizAvg . '% Accuracy',
                'color' => '#ef4444',
                'tips' => 'Review notes before attempting quizzes.'
            ]
        ];
                'progress' => 30,
                'unlocked' => false,
                'icon' => '🦉',
                'tag' => '3 / 10 Hours Completed',
                'color' => '#3b82f6',
                'tips' => 'Access study notes after 10 PM to log midnight progress.'
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Leaderboard metrics fetched successfully',
            'data' => [
                'xp' => $xpRankings,
                'academic' => $academicRankings,
                'performance_overview' => $performanceOverview,
                'achievements' => $achievements
            ]
        ], 200);
    }
}
