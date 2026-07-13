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
        Schema::create('recordings', function (Blueprint $table) {

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
            | RECORDING TOPIC
            |--------------------------------------------------------------------------
            */

            $table->string('topic', 100);

            /*
            |--------------------------------------------------------------------------
            | RECORDING DURATION
            |--------------------------------------------------------------------------
            */

            $table->integer('duration');

            /*
            |--------------------------------------------------------------------------
            | VIDEO LINK
            |--------------------------------------------------------------------------
            */

            $table->string('video_link');

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
        Schema::dropIfExists('recordings');
    }
};