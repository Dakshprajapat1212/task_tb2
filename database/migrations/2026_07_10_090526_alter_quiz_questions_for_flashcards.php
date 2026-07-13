<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
        });

        // Use raw SQL to modify columns to nullable to avoid DBAL
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE quiz_questions MODIFY option_a VARCHAR(255) NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE quiz_questions MODIFY option_b VARCHAR(255) NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE quiz_questions MODIFY option_c VARCHAR(255) NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE quiz_questions MODIFY option_d VARCHAR(255) NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE quiz_questions MODIFY correct_option ENUM('a', 'b', 'c', 'd') NULL");
    }

    public function down()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE quiz_questions MODIFY option_a VARCHAR(255) NOT NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE quiz_questions MODIFY option_b VARCHAR(255) NOT NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE quiz_questions MODIFY option_c VARCHAR(255) NOT NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE quiz_questions MODIFY option_d VARCHAR(255) NOT NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE quiz_questions MODIFY correct_option ENUM('a', 'b', 'c', 'd') NOT NULL");
        
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn('question_type');
            $table->dropColumn('correct_answer');
        });
    }
};
