<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Services\BadgeService;
use Illuminate\Database\Seeder;

class BackfillStudentBadgesSeeder extends Seeder
{
    /**
     * Evaluates and seeds baseline badges for all existing students using BadgeService.
     * Run ONCE after the create_student_badges_table migration.
     */
    public function run(): void
    {
        $students = Student::all();
        $this->command->info("Backfilling badges for {$students->count()} students...");

        $badgeService = new BadgeService();

        foreach ($students as $student) {
            $newlyUnlocked = $badgeService->checkAndAwardBadges($student);
            $totalBadges = $student->badges()->count();
            $titles = $student->badges()->pluck('title')->implode(', ');
            $this->command->line("  #{$student->id} ({$student->user?->name}): {$totalBadges} badges unlocked [{$titles}]");
        }

        $this->command->info("Student badges backfill complete!");
    }
}
