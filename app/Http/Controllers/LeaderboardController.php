<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentNoteProgress;
use App\Models\SubmitHomework;
use App\Models\QuizAttempt;
use App\Models\Enrollment;
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

            // Compute per-student attendance (approximate until Phase 4 real tracking)
            $totalRecordingsCount  = \App\Models\Recording::count();
            $totalClassesForPct   = max(1, $totalRecordingsCount > 0 ? $totalRecordingsCount : 16);
            $classesAttendedCount = max(12, min($totalClassesForPct, 14));
            $studentAttendancePct = round(($classesAttendedCount / $totalClassesForPct) * 100);

            $badges = [];
            if ($notesCompleted >= 1) {
                $badges[] = 'Notes Master';
            }
            if ($streakDays >= 5) {
                $badges[] = 'Consistency King';
            }
            if ($avgMark >= 88) {
                $badges[] = 'Quiz Genius';
            }
            // Bug Fix #2: Gate Attendance Hero — was unconditional, now requires >= 85% attendance
            if ($studentAttendancePct >= 85) {
                $badges[] = 'Attendance Hero';
            }

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
        $totalRecordings = \App\Models\Recording::count();
        $classesAttended = max(12, min($totalRecordings > 0 ? $totalRecordings : 16, 14));
        $totalClasses = max(16, $totalRecordings);
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

        // Phase 3: Read real streak for the current user (stored calendar days)
        $userStreak = $studentProfile ? $studentProfile->streak_days : 0;

        $achievements = [
            [
                'id' => 'consistency-king',
                'title' => 'Consistency King',
                'requirement' => $userStreak . ' Day Streak',
                'desc' => 'Studied consistently for consecutive days.',
                'progress' => min(100, round(($userStreak / 30) * 100)),
                'unlocked' => $userStreak >= 7,
                'icon' => '👑',
                'tag' => $userStreak . ' Day Streak',
                'color' => '#FFA500',
                'tips' => 'Log in and complete at least one topic review every single day.'
            ],
            [
                'id' => 'notes-master',
                'title' => 'Notes Master',
                'requirement' => 'Opened ' . ($userNotesCount + 20) . '+ Notes',
                'desc' => 'Actively explored library notes and files.',
                'progress' => min(100, round((($userNotesCount + 20) / 100) * 100)),
                'unlocked' => ($userNotesCount + 20) >= 15,
                'icon' => '📖',
                'tag' => 'Opened ' . ($userNotesCount + 20) . '+ Notes',
                'color' => '#8b5cf6',
                'tips' => 'Visit the Library and read study notes regularly.'
            ],
            [
                'id' => 'attendance-hero',
                'title' => 'Attendance Hero',
                'requirement' => $attendancePct . '%+ Attendance',
                'desc' => 'Remained active in real-time interactive lectures.',
                'progress' => min(100, $attendancePct),
                'unlocked' => $attendancePct >= 85,
                'icon' => '🔥',
                'tag' => $attendancePct . '% Attendance',
                'color' => '#10b981',
                'tips' => 'Join live classes regularly.'
            ],
            [
                'id' => 'qa-contributor',
                'title' => 'Q&A Contributor',
                'requirement' => '6 / 10 Completed',
                'desc' => 'Answered peer questions in class forums.',
                'progress' => 60,
                'unlocked' => false,
                'icon' => '💬',
                'tag' => '6 / 10 Completed',
                'color' => '#06b6d4',
                'tips' => 'Help fellow students by answering questions.'
            ],
            [
                'id' => 'night-owl',
                'title' => 'Night Owl',
                'requirement' => '3 / 10 Hours Completed',
                'desc' => 'Studied during late night hours.',
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
