<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\StudentNoteProgress;
use App\Models\SubmitHomework;
use App\Models\QuizAttempt;
use Illuminate\Database\Seeder;

class BackfillStudentXpSeeder extends Seeder
{
    /**
     * Backfill XP for existing students from historical activity data.
     * Run this ONCE after the add_xp_to_students_table migration.
     *
     * XP formula (matching Phase 2 real-time awards):
     *   +20 per note completed (StudentNoteProgress)
     *   +30 per quiz attempt (QuizAttempt)
     *   +homework.xp per approved homework submission (SubmitHomework)
     */
    public function run(): void
    {
        $students = Student::all();

        if ($students->isEmpty()) {
            $this->command->warn('No students found. Nothing to backfill.');
            return;
        }

        $this->command->info("Backfilling XP for {$students->count()} students...");

        foreach ($students as $student) {
            // +20 XP per completed note
            $notesXp = StudentNoteProgress::where('student_id', $student->id)->count() * 20;

            // +homework.xp per approved submission
            $homeworkXp = SubmitHomework::where('student_id', $student->id)
                ->where('status', 'approved')
                ->with('assignHomework')
                ->get()
                ->sum(fn($s) => $s->assignHomework->xp ?? 50);

            // +30 XP per quiz attempt
            $quizXp = QuizAttempt::where('student_id', $student->id)->count() * 30;

            $totalXp = $notesXp + $homeworkXp + $quizXp;

            $student->update(['xp' => $totalXp]);

            $this->command->line(
                "  Student #{$student->id} ({$student->user?->name}): " .
                "Notes={$notesXp} + Homework={$homeworkXp} + Quizzes={$quizXp} = {$totalXp} XP"
            );
        }

        $this->command->info('XP backfill complete!');
    }
}
