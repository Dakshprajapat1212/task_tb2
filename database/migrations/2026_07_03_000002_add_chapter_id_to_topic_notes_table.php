<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('topic_notes', function (Blueprint $table) {
            $table->foreignId('chapter_id')
                ->nullable()
                ->after('subject_id')
                ->constrained('chapters')
                ->nullOnDelete();
        });

        DB::table('topic_notes')
            ->join('chapters', function ($join) {
                $join->on('topic_notes.class_id', '=', 'chapters.class_id')
                    ->on('topic_notes.subject_id', '=', 'chapters.subject_id')
                    ->on('topic_notes.chapter', '=', 'chapters.title');
            })
            ->update(['topic_notes.chapter_id' => DB::raw('chapters.id')]);
    }

    public function down(): void
    {
        Schema::table('topic_notes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chapter_id');
        });
    }
};
