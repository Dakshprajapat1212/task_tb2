<?php

$file = 'app/Http/Controllers/V2AdminQuestionController.php';
$content = file_get_contents($file);

$content = str_replace('use App\Models\Quiz;', '', $content);

$indexMethod = <<<'METHOD'
    public function index(Request $request)
    {
        if (!in_array(auth()->user()->role_id, [2, 3])) return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);

        $query = QuizQuestion::query();

        if ($request->filled('chapter_id')) {
            $query->where('chapter_id', $request->chapter_id);
        }

        if ($request->filled('topic_note_id')) {
            $query->where('topic_note_id', $request->topic_note_id);
        }

        if (auth()->user()->role_id == 2) {
            $faculty = Faculty::where('user_id', auth()->id())->first();
            if (!$faculty) return response()->json(['success' => false, 'message' => 'Faculty profile not found'], 404);
            $class_ids = ClassModel::forFaculty($faculty->id)->pluck('id');
            
            $query->whereHas('chapter', function($q) use ($class_ids) {
                $q->whereIn('class_id', $class_ids);
            });
        }

        $questions = $query->orderBy('display_order')->get();

        return response()->json([
            'success' => true,
            'message' => 'Questions fetched successfully',
            'data' => $questions
        ], 200);
    }
METHOD;

// We'll replace index method manually
$content = preg_replace('/public function index\(Request \$request\).*?public function store/s', $indexMethod . "\n\n    public function store", $content);

$storeMethod = <<<'METHOD'
    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role_id, [2, 3])) return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);

        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'topic_note_id' => 'nullable|exists:topic_notes,id',
            'question' => 'required|string',
            'option_a' => 'nullable|string',
            'option_b' => 'nullable|string',
            'option_c' => 'nullable|string',
            'option_d' => 'nullable|string',
            'correct_option' => 'nullable|in:a,b,c,d',
            'correct_answer' => 'nullable|string',
            'difficulty_level' => 'nullable|in:Easy,Medium,Hard',
            'explanation' => 'nullable|string',
            'display_order' => 'nullable|integer'
        ]);

        $chapter = Chapter::find($request->chapter_id);
        if (!$this->canAccessChapter($chapter)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access to this Chapter'], 403);
        }

        $question = QuizQuestion::create([
            'chapter_id' => $request->chapter_id,
            'topic_note_id' => $request->topic_note_id,
            'question' => $request->question,
            'option_a' => $request->option_a,
            'option_b' => $request->option_b,
            'option_c' => $request->option_c,
            'option_d' => $request->option_d,
            'correct_option' => $request->correct_option,
            'correct_answer' => $request->correct_answer,
            'difficulty_level' => $request->difficulty_level ?? 'Medium',
            'explanation' => $request->explanation,
            'display_order' => $request->display_order ?? 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Question created successfully',
            'data' => $question
        ], 201);
    }
METHOD;

$content = preg_replace('/public function store\(Request \$request\).*?public function show/s', $storeMethod . "\n\n    public function show", $content);

$showMethod = <<<'METHOD'
    public function show($id)
    {
        $question = QuizQuestion::with('chapter', 'topicNote')->find($id);
        if (!$question) return response()->json(['success' => false, 'message' => 'Question not found'], 404);
        if (!$this->canAccessChapter($question->chapter)) return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);

        return response()->json([
            'success' => true,
            'message' => 'Question fetched successfully',
            'data' => $question
        ], 200);
    }
METHOD;

$content = preg_replace('/public function show\(\$id\).*?public function update/s', $showMethod . "\n\n    public function update", $content);

$updateMethod = <<<'METHOD'
    public function update(Request $request, $id)
    {
        $question = QuizQuestion::find($id);
        if (!$question) return response()->json(['success' => false, 'message' => 'Question not found'], 404);
        if (!$this->canAccessChapter($question->chapter)) return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);

        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'topic_note_id' => 'nullable|exists:topic_notes,id',
            'question' => 'required|string',
            'option_a' => 'nullable|string',
            'option_b' => 'nullable|string',
            'option_c' => 'nullable|string',
            'option_d' => 'nullable|string',
            'correct_option' => 'nullable|in:a,b,c,d',
            'correct_answer' => 'nullable|string',
            'difficulty_level' => 'nullable|in:Easy,Medium,Hard',
            'explanation' => 'nullable|string',
            'display_order' => 'nullable|integer'
        ]);

        if ($request->chapter_id != $question->chapter_id) {
            $newChapter = Chapter::find($request->chapter_id);
            if (!$this->canAccessChapter($newChapter)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access to destination Chapter'], 403);
            }
        }

        $question->update([
            'chapter_id' => $request->chapter_id,
            'topic_note_id' => $request->topic_note_id,
            'question' => $request->question,
            'option_a' => $request->option_a,
            'option_b' => $request->option_b,
            'option_c' => $request->option_c,
            'option_d' => $request->option_d,
            'correct_option' => $request->correct_option,
            'correct_answer' => $request->correct_answer,
            'difficulty_level' => $request->difficulty_level ?? $question->difficulty_level,
            'explanation' => $request->explanation,
            'display_order' => $request->display_order ?? $question->display_order
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Question updated successfully',
            'data' => $question
        ], 200);
    }
METHOD;

$content = preg_replace('/public function update\(Request \$request, \$id\).*?public function destroy/s', $updateMethod . "\n\n    public function destroy", $content);

$destroyMethod = <<<'METHOD'
    public function destroy($id)
    {
        $question = QuizQuestion::find($id);
        if (!$question) return response()->json(['success' => false, 'message' => 'Question not found'], 404);
        if (!$this->canAccessChapter($question->chapter)) return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);

        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully'
        ], 200);
    }
METHOD;

$content = preg_replace('/public function destroy\(\$id\).*?private function canAccessQuiz/s', $destroyMethod . "\n\n    private function canAccessChapter", $content);


$canAccessChapterMethod = <<<'METHOD'
    private function canAccessChapter(Chapter $chapter)
    {
        if (auth()->user()->role_id == 3) return true;
        if (auth()->user()->role_id == 2) {
            $faculty = Faculty::where('user_id', auth()->id())->first();
            if (!$faculty) return false;
            return ClassModel::forFaculty($faculty->id)->where('id', $chapter->class_id)->exists();
        }
        return false;
    }
}
METHOD;

$content = preg_replace('/private function canAccessQuiz.*?}$/s', $canAccessChapterMethod, $content);

file_put_contents($file, $content);

echo "V2AdminQuestionController updated.\n";
