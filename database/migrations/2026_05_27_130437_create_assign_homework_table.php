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
        Schema::create('assign_homeworks', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | PRIMARY KEY
            |--------------------------------------------------------------------------
            */

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | FOREIGN KEY -> CLASSES TABLE
            |--------------------------------------------------------------------------
            */

            $table->foreignId('class_id')

                  ->constrained('classes')

                  ->onDelete('cascade');

            /*
            |--------------------------------------------------------------------------
            | HOMEWORK DETAILS
            |--------------------------------------------------------------------------
            */

            $table->string('topic');

            $table->text('description')->nullable();

            $table->date('due_date')->nullable();

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $table->string('status')->default('active');

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
        Schema::dropIfExists('assign_homeworks');
    }
};