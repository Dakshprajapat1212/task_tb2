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
        Schema::table('assign_homeworks', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade');
            $table->integer('points')->default(100);
            $table->integer('xp')->default(50);
        });

        Schema::table('submit_homeworks', function (Blueprint $table) {
            $table->text('student_comment')->nullable();
        });

        Schema::create('homework_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assign_homework_id')->constrained('assign_homeworks')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('issue_type');
            $table->text('description');
            $table->string('status')->default('Under Review');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('homework_issues');

        Schema::table('submit_homeworks', function (Blueprint $table) {
            $table->dropColumn('student_comment');
        });

        Schema::table('assign_homeworks', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn(['subject_id', 'points', 'xp']);
        });
    }
};
