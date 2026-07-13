<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')
                ->constrained('classes')
                ->onDelete('cascade');
            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('display_order')->default(1);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        $existingTopics = DB::table('topic_notes')
            ->select('class_id', 'subject_id', 'chapter')
            ->whereNotNull('chapter')
            ->distinct()
            ->get();

        foreach ($existingTopics as $index => $noteTopic) {
            DB::table('chapters')->insert([
                'class_id' => $noteTopic->class_id,
                'subject_id' => $noteTopic->subject_id,
                'title' => $noteTopic->chapter,
                'description' => 'Migrated from existing notes.',
                'display_order' => $index + 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
