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
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['class_link', 'class_date', 'start_time', 'end_time']);
        });

        Schema::table('class_subjects', function (Blueprint $table) {
            $table->string('class_link', 100)->nullable();
            $table->date('class_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('stream_url', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->dropColumn(['class_link', 'class_date', 'start_time', 'end_time', 'stream_url']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->string('class_link', 100)->nullable();
            $table->date('class_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
        });
    }
};
