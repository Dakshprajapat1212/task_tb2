<?php

$file = 'app/Http/Controllers/LibraryController.php';
$content = file_get_contents($file);

// Replace submitQuiz and result with chapter and module variants
$submitQuizMethod = <<<'METHOD'
    public function submitQuiz(Request $request, Quiz $quiz)
    {
        $quiz->load(['chapter', 'topicNote', 'questions']);

        $classId = $quiz->chapter_id ? $quiz->chapter->class_id : ($quiz->topic_note_id ? $quiz->topicNote->class_id : null);

        if (!$classId || !$this->canAccessClass($classId)) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:quiz_questions,id',
            'answers.*.selected_option' => 'required|in:a,b,c,d',
        ]);

        $questions = $quiz->questions->keyBy('id');
        $submittedQuestionIds = collect($validated['answers'])
            ->pluck('question_id')
            ->unique()
            ->values();

        if (
            $submittedQuestionIds->count() !== $questions->count() ||
            $submittedQuestionIds->diff($questions->keys())->isNotEmpty()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Please submit exactly one answer for every quiz question'
            ], 422);
        }

        $score = 0;

        $attempt = DB::transaction(function () use ($validated, $questions, $quiz, &$score) {
            $attempt = QuizAttempt::create([
                'student_id' => $this->student()->id,
                'quiz_id' => $quiz->id,
                'score' => 0,
                'total_questions' => $questions->count(),
                'completed_at' => now(),
            ]);

            foreach ($validated['answers'] as $answer) {
                $question = $questions->get($answer['question_id']);

                if (!$question) {
                    continue;
                }

                $isCorrect = $question->correct_option === $answer['selected_option'];

                if ($isCorrect) {
                    $score++;
                }

                QuizAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'selected_option' => $answer['selected_option'],
                    'is_correct' => $isCorrect,
                ]);
            }

            $attempt->update(['score' => $score]);

            return $attempt;
        });

        $attempt->load('answers.question', 'quiz');

        return response()->json([
            'success' => true,
            'message' => 'Quiz submitted successfully',
            'data' => $this->attemptResult($attempt)
        ], 201);
    }
METHOD;

$resultMethod = <<<'METHOD'
    public function result(Quiz $quiz)
    {
        $quiz->load(['chapter', 'topicNote']);

        $classId = $quiz->chapter_id ? $quiz->chapter->class_id : ($quiz->topic_note_id ? $quiz->topicNote->class_id : null);

        if (!$classId || !$this->canAccessClass($classId)) {
            return $this->unauthorized();
        }

        $attempt = QuizAttempt::where('student_id', $this->student()->id)
            ->where('quiz_id', $quiz->id)
            ->with('answers.question', 'quiz')
            ->latest()
            ->first();

        if (!$attempt) {
            return $this->notFound('Quiz result not found');
        }

        return response()->json([
            'success' => true,
            'message' => 'Quiz result fetched successfully',
            'data' => $this->attemptResult($attempt)
        ], 200);
    }
METHOD;

$newMethods = <<<'METHOD'
    public function submitChapterQuiz(Request $request, Chapter $chapter)
    {
        if (!$this->canAccessClass($chapter->class_id)) return $this->unauthorized();
        $questions = $chapter->quizQuestions()->get()->keyBy('id');
        return $this->processSubmission($request, $questions, $chapter->id, null);
    }

    public function submitModuleQuiz(Request $request, TopicNote $note)
    {
        if (!$this->canAccessClass($note->class_id)) return $this->unauthorized();
        $questions = $note->quizQuestions()->get()->keyBy('id');
        return $this->processSubmission($request, $questions, null, $note->id);
    }

    private function processSubmission(Request $request, $questions, $chapterId, $noteId)
    {
        if ($questions->isEmpty()) return $this->notFound('No questions found for this quiz');

        $validated = $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:quiz_questions,id',
            'answers.*.selected_option' => 'required|in:a,b,c,d',
        ]);

        $submittedQuestionIds = collect($validated['answers'])->pluck('question_id')->unique()->values();

        if ($submittedQuestionIds->count() !== $questions->count() || $submittedQuestionIds->diff($questions->keys())->isNotEmpty()) {
            return response()->json(['success' => false, 'message' => 'Please submit exactly one answer for every quiz question'], 422);
        }

        $score = 0;
        $attempt = DB::transaction(function () use ($validated, $questions, $chapterId, $noteId, &$score) {
            $attempt = QuizAttempt::create([
                'student_id' => $this->student()->id,
                'chapter_id' => $chapterId,
                'topic_note_id' => $noteId,
                'score' => 0,
                'total_questions' => $questions->count(),
                'completed_at' => now(),
            ]);

            foreach ($validated['answers'] as $answer) {
                $question = $questions->get($answer['question_id']);
                if (!$question) continue;

                $isCorrect = $question->correct_option === $answer['selected_option'];
                if ($isCorrect) $score++;

                QuizAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'selected_option' => $answer['selected_option'],
                    'is_correct' => $isCorrect,
                ]);
            }

            $attempt->update(['score' => $score]);
            return $attempt;
        });

        $attempt->load('answers.question');
        return response()->json(['success' => true, 'message' => 'Quiz submitted successfully', 'data' => $this->attemptResult($attempt)], 201);
    }

    public function chapterQuizResult(Chapter $chapter)
    {
        if (!$this->canAccessClass($chapter->class_id)) return $this->unauthorized();
        return $this->fetchResult($chapter->id, null);
    }

    public function moduleQuizResult(TopicNote $note)
    {
        if (!$this->canAccessClass($note->class_id)) return $this->unauthorized();
        return $this->fetchResult(null, $note->id);
    }

    private function fetchResult($chapterId, $noteId)
    {
        $attempt = QuizAttempt::where('student_id', $this->student()->id)
            ->where('chapter_id', $chapterId)
            ->where('topic_note_id', $noteId)
            ->with('answers.question')
            ->latest()
            ->first();

        if (!$attempt) return $this->notFound('Quiz result not found');

        return response()->json([
            'success' => true,
            'message' => 'Quiz result fetched successfully',
            'data' => $this->attemptResult($attempt)
        ], 200);
    }
METHOD;

$content = str_replace($submitQuizMethod, $newMethods, $content);
$content = str_replace($resultMethod, '', $content);

// Also need to update attemptResult to not use quiz->passing_marks
$attemptResultMethod = <<<'METHOD'
    private function attemptResult(QuizAttempt $attempt): array
    {
        $percentage = $attempt->total_questions > 0
            ? round(($attempt->score / $attempt->total_questions) * 100, 2)
            : 0;

        return [
            'attempt_id' => $attempt->id,
            'quiz_id' => $attempt->quiz_id,
            'score' => $attempt->score,
            'total_questions' => $attempt->total_questions,
            'correct_answers' => $attempt->answers->where('is_correct', true)->count(),
            'wrong_answers' => $attempt->answers->where('is_correct', false)->count(),
            'percentage' => $percentage,
            'result' => $attempt->score >= $attempt->quiz->passing_marks ? 'pass' : 'fail',
            'completed_at' => $attempt->completed_at,
        ];
    }
METHOD;

$newAttemptResultMethod = <<<'METHOD'
    private function attemptResult(QuizAttempt $attempt): array
    {
        $percentage = $attempt->total_questions > 0
            ? round(($attempt->score / $attempt->total_questions) * 100, 2)
            : 0;

        return [
            'attempt_id' => $attempt->id,
            'chapter_id' => $attempt->chapter_id,
            'topic_note_id' => $attempt->topic_note_id,
            'score' => $attempt->score,
            'total_questions' => $attempt->total_questions,
            'correct_answers' => $attempt->answers->where('is_correct', true)->count(),
            'wrong_answers' => $attempt->answers->where('is_correct', false)->count(),
            'percentage' => $percentage,
            'result' => $percentage >= 60 ? 'pass' : 'fail', // Globally passing marks is 60%
            'completed_at' => $attempt->completed_at,
        ];
    }
METHOD;

$content = str_replace($attemptResultMethod, $newAttemptResultMethod, $content);

file_put_contents($file, $content);

echo "LibraryController rewritten.\n";

