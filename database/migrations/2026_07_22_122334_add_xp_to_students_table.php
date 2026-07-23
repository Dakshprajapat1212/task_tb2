<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds a persistent XP counter to students table.
     * XP is now awarded at action time instead of recalculated on every leaderboard call.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Total accumulated XP — updated in real-time on every XP-earning action
            $table->unsignedInteger('xp')->default(0)->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('xp');
        });
    }
};
