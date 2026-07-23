<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Recording;
use App\Models\LiveAttendance;
use App\Models\Enrollment;
use Illuminate\Database\Seeder;

class BackfillLiveAttendanceSeeder extends Seeder
{
    /**
     * Seed baseline live attendance for existing students.
     * Run ONCE after the create_live_attendances_table migration.
     */
    public function run(): void
    {
        $students = Student::all();
        $this->command->info("Backfilling live attendance for {$students->count()} students...");

        foreach ($students as $student) {
            $classIds = Enrollment::where('user_id', $student->user_id)
                ->where('status', 'approved')
                ->pluck('class_id');

            $recordings = Recording::whereIn('class_id', $classIds)->get();

            if ($recordings->isEmpty()) {
                // If no recordings exist in DB, create mock attendances for class_id 1
                for ($i = 1; $i <= 10; $i++) {
                    LiveAttendance::updateOrCreate([
                        'student_id' => $student->id,
                        'class_id' => 1,
                        'subject_id' => ($i % 3) + 1,
                        'class_date' => now()->subDays($i)->toDateString(),
                    ], [
                        'joined_at' => now()->subDays($i)->subMinutes(30),
                        'completed_at' => now()->subDays($i),
                        'duration_seconds' => 1800,
                    ]);
                }
            } else {
                // Attend ~80% of recordings
                foreach ($recordings as $rec) {
                    if (rand(1, 10) <= 8) {
                        LiveAttendance::updateOrCreate([
                            'student_id' => $student->id,
                            'class_id' => $rec->class_id,
                            'subject_id' => $rec->subject_id ?? 1,
                            'class_date' => $rec->created_at ? $rec->created_at->toDateString() : now()->toDateString(),
                        ], [
                            'joined_at' => now()->subMinutes(35),
                            'completed_at' => now(),
                            'duration_seconds' => 1800,
                        ]);
                    }
                }
            }

            $count = LiveAttendance::where('student_id', $student->id)->count();
            $this->command->line("  #{$student->id} ({$student->user?->name}): Backfilled {$count} live attendance logs.");
        }

        $this->command->info("Live attendance seeding completed!");
    }
}
