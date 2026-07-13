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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('gender')->nullable();
            $table->string('photo')->nullable();
            $table->string('school')->nullable();
            $table->string('board')->nullable();
            $table->string('course')->nullable();
            $table->string('batch_mode')->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('parent_mobile')->nullable();
            $table->string('marksheet')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'full_name', 'email', 'mobile', 'gender', 'photo', 
                'school', 'board', 'course', 'batch_mode', 
                'father_name', 'father_occupation', 'mother_name', 
                'parent_mobile', 'marksheet'
            ]);
        });
    }
};
