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
        Schema::create('submit_homeworks', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | PRIMARY KEY
            |--------------------------------------------------------------------------
            */

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | ASSIGNED HOMEWORK REFERENCE
            |--------------------------------------------------------------------------
            */

            $table->foreignId('assign_homework_id')

                  ->constrained('assign_homeworks')

                  ->onDelete('cascade');

            /*
            |--------------------------------------------------------------------------
            | STUDENT REFERENCE
            |--------------------------------------------------------------------------
            */

            $table->foreignId('student_id')

                  ->constrained('students')

                  ->onDelete('cascade');

            /*
            |--------------------------------------------------------------------------
            | SUBMITTED FILE
            |--------------------------------------------------------------------------
            */

            $table->string('file');

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $table->string('status')->default('pending');

            /*
            |--------------------------------------------------------------------------
            | FACULTY FEEDBACK
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')->nullable();

            /*
            |--------------------------------------------------------------------------
            | TIMESTAMPS
            |--------------------------------------------------------------------------
            */

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
        Schema::dropIfExists('submit_homeworks');
    }
};