<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds real calendar-day streak tracking columns to the students table.
     * Phase 3 replaces the fake approximation formula with these stored values.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Running streak in consecutive calendar days
            // Incremented when active on consecutive days, reset to 1 on a gap
            $table->unsignedInteger('streak_days')->default(0)->after('xp');

            // Last date the student did any study activity (note / quiz / homework submission)
            // null = never active
            $table->date('last_activity_date')->nullable()->after('streak_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['streak_days', 'last_activity_date']);
        });
    }
};
