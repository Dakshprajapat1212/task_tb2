<?php

namespace App\Services;

use App\Models\Student;

class StudentStreakService
{
    /**
     * Call this after any study action (note completion, quiz attempt, homework submission).
     *
     * Logic:
     * - If student was already active today → do nothing (idempotent)
     * - If student was active yesterday → extend streak (streak_days++)
     * - Otherwise (missed ≥1 day, or never active) → reset streak to 1
     *
     * Milestone XP bonuses are awarded when streak hits exactly 7, 14, or 30 days.
     *
     * @return int XP bonus awarded this call (0 if no milestone hit today)
     */
    public function updateStreak(Student $student): int
    {
        $today     = now()->toDateString();           // e.g. "2026-07-23"
        $last      = $student->last_activity_date     // Carbon instance or null
                        ?->toDateString();

        // Already active today — streak is current, nothing to change
        if ($last === $today) {
            return 0;
        }

        $yesterday = now()->subDay()->toDateString(); // e.g. "2026-07-22"

        if ($last === $yesterday) {
            // Consecutive day — extend streak
            $student->increment('streak_days');
        } else {
            // Missed one or more days (or never active before) — reset to 1
            $student->streak_days = 1;
        }

        // Persist the updated last_activity_date
        $student->last_activity_date = $today;
        $student->save();

        // Award XP milestone bonuses at key streak days
        $bonusXp = 0;
        $currentStreak = $student->fresh()->streak_days;

        if (in_array($currentStreak, [7, 14, 30])) {
            $bonusXp = 50;
            $student->increment('xp', $bonusXp);
        }

        return $bonusXp;
    }
}
