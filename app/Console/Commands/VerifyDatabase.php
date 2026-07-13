<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VerifyDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("--- CHAPTER CONSTRAINT ---");
        $this->info("Total Question Banks: " . \Illuminate\Support\Facades\DB::table('quizzes')->count());
        $this->info("NULL chapter: " . \Illuminate\Support\Facades\DB::table('quizzes')->whereNull('chapter_id')->count());
        $this->info("Invalid chapter_id (Does not exist in chapters): " . \Illuminate\Support\Facades\DB::table('quizzes')->whereNotNull('chapter_id')->whereNotIn('chapter_id', function($q) { $q->select('id')->from('chapters'); })->count());

        $this->info("--- TOPIC NOTE RELATIONSHIP ---");
        $this->info("Topic Notes pointing to missing Chapters: " . \Illuminate\Support\Facades\DB::table('notes')->whereNotNull('chapter_id')->whereNotIn('chapter_id', function($q) { $q->select('id')->from('chapters'); })->count());
        $this->info("Question Banks pointing to Topic Notes from another Chapter: " . \Illuminate\Support\Facades\DB::table('quizzes')->join('notes', 'quizzes.topic_note_id', '=', 'notes.id')->whereColumn('quizzes.chapter_id', '!=', 'notes.chapter_id')->count());

        $this->info("--- DIFFICULTY MIGRATION ---");
        $this->info("Easy: " . \Illuminate\Support\Facades\DB::table('quiz_questions')->where('difficulty_level', 'Easy')->count());
        $this->info("Medium: " . \Illuminate\Support\Facades\DB::table('quiz_questions')->where('difficulty_level', 'Medium')->count());
        $this->info("Hard: " . \Illuminate\Support\Facades\DB::table('quiz_questions')->where('difficulty_level', 'Hard')->count());
        $this->info("NULL: " . \Illuminate\Support\Facades\DB::table('quiz_questions')->whereNull('difficulty_level')->count());
        
        return Command::SUCCESS;
    }
}
