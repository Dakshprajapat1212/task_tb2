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
        Schema::create('topic_notes', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | PRIMARY KEY
            |--------------------------------------------------------------------------
            */

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | CLASS REFERENCE
            |--------------------------------------------------------------------------
            */

            $table->foreignId('class_id')

                  ->constrained('classes')

                  ->onDelete('cascade');

            /*
            |--------------------------------------------------------------------------
            | SUBJECT REFERENCE
            |--------------------------------------------------------------------------
            */

            $table->foreignId('subject_id')

                  ->constrained('subjects')

                  ->onDelete('cascade');

            /*
            |--------------------------------------------------------------------------
            | NOTE TOPIC
            |--------------------------------------------------------------------------
            */

            $table->string('chapter', 100);

            /*
            |--------------------------------------------------------------------------
            | FILE URL
            |--------------------------------------------------------------------------
            */

            $table->string('file_url');

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
        Schema::dropIfExists('topic_notes');
    }
};