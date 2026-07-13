use Illuminate\Support\Facades\DB;

echo "--- CHAPTER CONSTRAINT ---" . PHP_EOL;
echo "Total Question Banks: " . DB::table('quizzes')->count() . PHP_EOL;
echo "NULL chapter: " . DB::table('quizzes')->whereNull('topic_id')->count() . PHP_EOL;
echo "Invalid chapter_id (Does not exist in topics): " . DB::table('quizzes')->whereNotNull('topic_id')->whereNotIn('topic_id', function($q) { $q->select('id')->from('topics'); })->count() . PHP_EOL;

echo "--- TOPIC NOTE RELATIONSHIP ---" . PHP_EOL;
echo "Topic Notes pointing to missing Chapters: " . DB::table('notes')->whereNotNull('topic_id')->whereNotIn('topic_id', function($q) { $q->select('id')->from('topics'); })->count() . PHP_EOL;
echo "Question Banks pointing to Topic Notes from another Chapter: " . DB::table('quizzes')->join('notes', 'quizzes.note_id', '=', 'notes.id')->whereColumn('quizzes.topic_id', '!=', 'notes.topic_id')->count() . PHP_EOL;

echo "--- DIFFICULTY MIGRATION ---" . PHP_EOL;
echo "Easy: " . DB::table('quiz_questions')->where('difficulty_level', 'Easy')->count() . PHP_EOL;
echo "Medium: " . DB::table('quiz_questions')->where('difficulty_level', 'Medium')->count() . PHP_EOL;
echo "Hard: " . DB::table('quiz_questions')->where('difficulty_level', 'Hard')->count() . PHP_EOL;
echo "NULL: " . DB::table('quiz_questions')->whereNull('difficulty_level')->count() . PHP_EOL;

