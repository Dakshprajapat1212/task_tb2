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
        // Assign chapter_id based on topic_note_id if chapter_id is NULL
        \Illuminate\Support\Facades\DB::statement('
            UPDATE quizzes q
            JOIN topic_notes n ON q.topic_note_id = n.id
            SET q.chapter_id = n.chapter_id
            WHERE q.chapter_id IS NULL AND q.topic_note_id IS NOT NULL
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
