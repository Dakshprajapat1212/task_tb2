<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('faculty_id')
                  ->constrained('faculties')
                  ->onDelete('cascade');

            $table->foreignId('subject_id')
                  ->constrained('subjects')
                  ->onDelete('cascade');

            $table->string('name', 20);

            $table->string('class_link', 100);

            $table->date('class_date');

            $table->time('start_time');

            $table->time('end_time');

            $table->timestamps();
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};