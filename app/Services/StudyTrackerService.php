<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentStudyLog;
use Carbon\Carbon;

class StudyTrackerService
{
    /**
     * Records or increments study duration for a student on a target date.
     */
    public function logStudyTime(
        Student $student,
        int $seconds,
        string $activityType = 'general',
        ?string $date = null
    ): StudentStudyLog {
        $targetDate = $date ?? Carbon::now()->toDateString();

        $log = StudentStudyLog::firstOrCreate([
            'student_id'    => $student->id,
            'date'          => $targetDate,
            'activity_type' => $activityType,
        ], [
            'duration_seconds' => 0,
        ]);

        $log->increment('duration_seconds', $seconds);

        return $log;
    }

    /**
     * Calculates weekly study hours (Mon-Sun) and total study hours.
     */
    public function getWeeklyAndTotalStats(Student $student): array
    {
        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek(); // Monday
        $endOfWeek = $now->copy()->endOfWeek();     // Sunday

        // Fetch logs for current week
        $weeklyLogs = StudentStudyLog::where('student_id', $student->id)
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->get();

        // Map Monday to Sunday
        $daysMap = [
            Carbon::MONDAY    => 'Mon',
            Carbon::TUESDAY   => 'Tue',
            Carbon::WEDNESDAY => 'Wed',
            Carbon::THURSDAY  => 'Thu',
            Carbon::FRIDAY    => 'Fri',
            Carbon::SATURDAY  => 'Sat',
            Carbon::SUNDAY    => 'Sun',
        ];

        $dailySeconds = [
            'Mon' => 0,
            'Tue' => 0,
            'Wed' => 0,
            'Thu' => 0,
            'Fri' => 0,
            'Sat' => 0,
            'Sun' => 0,
        ];

        foreach ($weeklyLogs as $log) {
            $dayName = Carbon::parse($log->date)->format('D'); // e.g. Mon, Tue, Wed
            if (isset($dailySeconds[$dayName])) {
                $dailySeconds[$dayName] += $log->duration_seconds;
            }
        }

        $weeklyHours = [];
        foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day) {
            $hrs = round($dailySeconds[$day] / 3600, 1);
            $weeklyHours[] = [
                'day' => $day,
                'hrs' => $hrs
            ];
        }

        // Total hours across all time
        $totalSeconds = StudentStudyLog::where('student_id', $student->id)->sum('duration_seconds');
        $totalHours = round($totalSeconds / 3600, 1);

        return [
            'weekly_hours' => $weeklyHours,
            'total_hours'  => $totalHours
        ];
    }
}
