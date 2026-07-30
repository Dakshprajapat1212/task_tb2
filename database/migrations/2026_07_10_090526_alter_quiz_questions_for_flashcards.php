<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->enum('question_type', ['mcq', 'flashcard'])->default('mcq')->after('question');
            $table->text('correct_answer')->nullable()->after('correct_option');

            // Make option columns and correct_option nullable
            $table->string('option_a')->nullable()->change();
            $table->string('option_b')->nullable()->change();
            $table->string('option_c')->nullable()->change();
            $table->string('option_d')->nullable()->change();
            $table->string('correct_option')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->string('option_a')->nullable(false)->change();
            $table->string('option_b')->nullable(false)->change();
            $table->string('option_c')->nullable(false)->change();
            $table->string('option_d')->nullable(false)->change();
            $table->string('correct_option')->nullable(false)->change();

            $table->dropColumn(['question_type', 'correct_answer']);
        });
    }
};