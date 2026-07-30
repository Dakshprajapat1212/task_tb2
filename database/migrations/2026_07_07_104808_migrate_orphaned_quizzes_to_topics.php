<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Assign chapter_id based on topic_note_id if chapter_id is NULL
        DB::statement('
            UPDATE quizzes
            SET chapter_id = (
                SELECT chapter_id 
                FROM topic_notes 
                WHERE topic_notes.id = quizzes.topic_note_id
            )
            WHERE chapter_id IS NULL AND topic_note_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Irreversible data migration, no schema changes to drop
    }
};