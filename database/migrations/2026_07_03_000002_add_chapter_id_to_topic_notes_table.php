<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('topic_notes', function (Blueprint $table) {
            $table->foreignId('chapter_id')
                ->nullable()
                ->after('subject_id')
                ->constrained('chapters')
                ->nullOnDelete();
        });

        // Cross-database compatible backfill (SQLite, MySQL, PostgreSQL)
        $chapters = DB::table('chapters')->get(['id', 'class_id', 'subject_id', 'title']);

        foreach ($chapters as $chapter) {
            DB::table('topic_notes')
                ->where('class_id', $chapter->class_id)
                ->where('subject_id', $chapter->subject_id)
                ->where('chapter', $chapter->title)
                ->whereNull('chapter_id')
                ->update(['chapter_id' => $chapter->id]);
        }
    }

    public function down(): void
    {
        Schema::table('topic_notes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chapter_id');
        });
    }
};