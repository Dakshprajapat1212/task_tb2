<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('doubts', function (Blueprint $table) {
            $table->text('explanation')->nullable()->after('answer');
        });

        // Add FullText indices using raw SQL since Schema builder doesn't perfectly handle FULLTEXT across all engines
        DB::statement('ALTER TABLE doubts ADD FULLTEXT INDEX doubt_question_fulltext (question)');
        DB::statement('ALTER TABLE quiz_questions ADD FULLTEXT INDEX quiz_question_fulltext (question)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE doubts DROP INDEX doubt_question_fulltext');
        DB::statement('ALTER TABLE quiz_questions DROP INDEX quiz_question_fulltext');

        Schema::table('doubts', function (Blueprint $table) {
            $table->dropColumn('explanation');
        });
    }
};
