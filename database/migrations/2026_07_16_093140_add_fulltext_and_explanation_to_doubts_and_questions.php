<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
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

        // Execute FULLTEXT indexing only on database drivers that support it (e.g., MySQL)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE doubts ADD FULLTEXT INDEX doubt_question_fulltext (question)');
            DB::statement('ALTER TABLE quiz_questions ADD FULLTEXT INDEX quiz_question_fulltext (question)');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE doubts DROP INDEX doubt_question_fulltext');
            DB::statement('ALTER TABLE quiz_questions DROP INDEX quiz_question_fulltext');
        }

        Schema::table('doubts', function (Blueprint $table) {
            $table->dropColumn('explanation');
        });
    }
};