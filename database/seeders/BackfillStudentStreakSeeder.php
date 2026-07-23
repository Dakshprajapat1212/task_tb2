<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\StudentNoteProgress;
use App\Models\SubmitHomework;
use App\Models\QuizAttempt;
use Illuminate\Database\Seeder;

class BackfillStudentStreakSeeder extends Seeder
{
    /**
     * Seed realistic baseline streaks for existing students.
     * Run ONCE after the add_streak_to_students_table migration.
     *
     * Since we have no historical daily activity logs, we can't reconstruct
     * real consecutive-day history. Instead:
     *   - Students with any activity → streak_days = 1, last_activity_date = today
     *   - Students with no activity  → streak_days = 0, last_activity_date = null
     *
     * From today onwards, streaks build naturally through real actions.
     */
    public function run(): void
    {
        $students = Student::all();

        if ($students->isEmpty()) {
            $this->command->warn('No students found. Nothing to backfill.');
            return;
        }

        $this->command->info("Seeding streak baseline for {$students->count()} students...");

        foreach ($students as $student) {
            $hasActivity =
                StudentNoteProgress::where('student_id', $student->id)->exists() ||
                SubmitHomework::where('student_id', $student->id)->exists()      ||
                QuizAttempt::where('student_id', $student->id)->exists();

            if ($hasActivity) {
                $student->update([
                    'streak_days'        => 1,
                    'last_activity_date' => now()->toDateString(),
                ]);
                $this->command->line("  #{$student->id} ({$student->user?->name}): streak_days=1, last_activity=today");
            } else {
                $student->update([
                    'streak_days'        => 0,
                    'last_activity_date' => null,
                ]);
                $this->command->line("  #{$student->id} ({$student->user?->name}): streak_days=0 (no activity)");
            }
        }

        $this->command->info('Streak backfill complete!');
    }
}
