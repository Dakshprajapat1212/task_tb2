<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_topic_note_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('students')
                ->onDelete('cascade');
            $table->foreignId('topic_note_id')
                ->constrained('topic_notes')
                ->onDelete('cascade');
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->unique(['student_id', 'topic_note_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_topic_note_progress');
    }
};
