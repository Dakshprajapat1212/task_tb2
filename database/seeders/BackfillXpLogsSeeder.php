<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\StudentNoteProgress;
use App\Models\SubmitHomework;
use App\Models\QuizAttempt;
use App\Models\LiveAttendance;
use App\Models\XpLog;
use Illuminate\Database\Seeder;

class BackfillXpLogsSeeder extends Seeder
{
    /**
     * Backfills itemized XpLog audit records for existing student activities.
     * Run ONCE after the create_xp_logs_table migration.
     */
    public function run(): void
    {
        $students = Student::all();
        $this->command->info("Backfilling XP audit logs for {$students->count()} students...");

        foreach ($students as $student) {
            // 1. Notes completion logs (+20 XP each)
            $notes = StudentNoteProgress::where('student_id', $student->id)->get();
            foreach ($notes as $note) {
                XpLog::firstOrCreate([
                    'student_id'   => $student->id,
                    'source'       => 'note',
                    'reference_id' => $note->topic_note_id,
                ], [
                    'amount'      => 20,
                    'description' => 'Completed study note',
                    'created_at'  => $note->completed_at ?? now(),
                ]);
            }

            // 2. Approved Homework logs (+50 XP each)
            $homeworks = SubmitHomework::where('student_id', $student->id)
                ->where('status', 'approved')
                ->with('assignHomework')
                ->get();

            foreach ($homeworks as $hw) {
                XpLog::firstOrCreate([
                    'student_id'   => $student->id,
                    'source'       => 'homework',
                    'reference_id' => $hw->id,
                ], [
                    'amount'      => $hw->assignHomework->xp ?? 50,
                    'description' => 'Approved homework submission',
                    'created_at'  => $hw->updated_at ?? now(),
                ]);
            }

            // 3. Quiz Attempts logs (+30 XP each)
            $quizzes = QuizAttempt::where('student_id', $student->id)->get();
            foreach ($quizzes as $quiz) {
                XpLog::firstOrCreate([
                    'student_id'   => $student->id,
                    'source'       => 'quiz',
                    'reference_id' => $quiz->id,
                ], [
                    'amount'      => 30,
                    'description' => 'Completed quiz assessment',
                    'created_at'  => $quiz->created_at ?? now(),
                ]);
            }

            // 4. Live Attendance logs (+20 XP each)
            $attendances = LiveAttendance::where('student_id', $student->id)->whereNotNull('completed_at')->get();
            foreach ($attendances as $att) {
                XpLog::firstOrCreate([
                    'student_id'   => $student->id,
                    'source'       => 'live_class',
                    'reference_id' => $att->id,
                ], [
                    'amount'      => 20,
                    'description' => 'Attended live class session',
                    'created_at'  => $att->completed_at ?? now(),
                ]);
            }

            $count = XpLog::where('student_id', $student->id)->count();
            $this->command->line("  #{$student->id} ({$student->user?->name}): Backfilled {$count} XP audit log records.");
        }

        $this->command->info("XP logs backfill complete!");
    }
}
