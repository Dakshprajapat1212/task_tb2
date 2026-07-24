<?php

namespace App\Http\Controllers;

use App\Http\Resources\FlashcardResource;
use App\Http\Resources\LibraryClassResource;
use App\Http\Resources\LibraryNoteResource;
use App\Http\Resources\LibrarySubjectResource;
use App\Http\Resources\QuizResource;
use App\Http\Resources\ChapterResource;
use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Models\TopicNote;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use App\Models\Student;
use App\Models\StudentNoteProgress;
use App\Models\Chapter;
use App\Models\Doubt;
use App\Services\StudentStreakService;
use App\Services\BadgeService;
use App\Services\XpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibraryController extends Controller
{
    public function classes()
    {
        $classIds = $this->approvedClassIds();

        $classes = ClassModel::whereIn('id', $classIds)
            ->with('subjects')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Library classes fetched successfully',
            'data' => LibraryClassResource::collection($classes)
        ], 200);
    }

    public function subjects($classId)
    {
        $class = ClassModel::with('subjects')->find($classId);

        if (!$class) {
            return $this->notFound('Class not found');
        }

        if (!$this->canAccessClass($class->id)) {
            return $this->unauthorized();
        }

        return response()->json([
            'success' => true,
            'message' => 'Library subjects fetched successfully',
            'data' => [
                'class' => new LibraryClassResource($class),
                'subjects' => LibrarySubjectResource::collection($class->subjects)
            ]
        ], 200);
    }

    public function chapters($classId, $subjectId)
    {
        $class = ClassModel::find($classId);

        if (!$class) {
            return $this->notFound('Class not found');
        }

        if (!$this->canAccessClass($class->id)) {
            return $this->unauthorized();
        }

        $chapters = Chapter::where('class_id', $class->id)
            ->where('subject_id', $subjectId)
            ->where('status', 'active')
            ->withCount('topicNotes')
            ->orderBy('display_order')
            ->orderBy('title')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Library chapters fetched successfully',
            'data' => ChapterResource::collection($chapters)
        ], 200);
    }

    public function notes(Chapter $chapter)
    {
        if (!$this->canAccessClass($chapter->class_id)) {
            return $this->unauthorized();
        }

        $student = $this->student();

        $notes = $chapter->topicNotes()
            ->leftJoin('student_topic_note_progress', function ($join) use ($student) {
                $join->on('topic_notes.id', '=', 'student_topic_note_progress.topic_note_id')
                    ->where('student_topic_note_progress.student_id', '=', $student->id);
            })
            ->select('topic_notes.*', 'student_topic_note_progress.completed_at')
            ->orderBy('topic_notes.id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Topic notes fetched successfully',
            'data' => LibraryNoteResource::collection($notes)
        ], 200);
    }

    public function note(TopicNote $note)
    {
        if (!$this->canAccessClass($note->class_id)) {
            return $this->unauthorized();
        }

        return response()->json([
            'success' => true,
            'message' => 'Library note fetched successfully',
            'data' => new LibraryNoteResource($note)
        ], 200);
    }

    public function downloadNote(TopicNote $note)
    {
        if (!$this->canAccessClass($note->class_id)) {
            return $this->unauthorized();
        }

        $path = storage_path('app/public/' . $note->file_url);

        if (!file_exists($path)) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }

        return response()->download($path, $note->file_url, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            'Content-Disposition' => 'attachment; filename="' . $note->file_url . '"'
        ]);
    }

    public function completeNote(TopicNote $note)
    {
        if (!$this->canAccessClass($note->class_id)) {
            return $this->unauthorized();
        }

        $student = $this->student();

        $progress = StudentNoteProgress::updateOrCreate(
            [
                'student_id'    => $student->id,
                'topic_note_id' => $note->id,
            ],
            [
                'completed_at' => now(),
            ]
        );

        // Award +20 XP only the FIRST time this note is completed
        // wasRecentlyCreated = true on INSERT, false on UPDATE (re-completion)
        if ($progress->wasRecentlyCreated) {
            (new XpService())->awardXp($student, 20, 'note', 'Completed study note: ' . ($note->title ?? 'Note'), $note->id);
        }

        // Update streak — any note interaction (even re-completion) counts as study activity
        (new StudentStreakService())->updateStreak($student);

        // Evaluate and award any eligible badges
        (new BadgeService())->checkAndAwardBadges($student);

        $topicProgress = $note->chapter_id
            ? $this->topicProgressData(Chapter::find($note->chapter_id), $student)
            : null;

        return response()->json([
            'success' => true,
            'message' => 'Note marked as completed',
            'data' => [
                'progress'       => $progress,
                'topic_progress' => $topicProgress,
                'xp_awarded'     => $progress->wasRecentlyCreated ? 20 : 0,
            ]
        ], 200);
    }

    public function progress(Chapter $chapter)
    {
        if (!$this->canAccessClass($chapter->class_id)) {
            return $this->unauthorized();
        }

        return response()->json([
            'success' => true,
            'message' => 'Topic progress fetched successfully',
            'data' => $this->topicProgressData($chapter, $this->student())
        ], 200);
    }

    public function quiz(Chapter $chapter)
    {
        if (!$this->canAccessClass($chapter->class_id)) {
            return $this->unauthorized();
        }

        $questions = $chapter->quizQuestions()
            ->orderBy('display_order')
            ->get();

        if ($questions->isEmpty()) {
            return $this->notFound('No quiz questions found for this chapter');
        }

        return response()->json([
            'success' => true,
            'message' => 'Chapter quiz fetched successfully',
            'data' => [
                'id' => $chapter->id,
                'title' => $chapter->title . ' - Final Quiz',
                'passing_marks_percentage' => 60,
                'questions' => $questions
            ]
        ], 200);
    }

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

        // Award +30 XP for completing this quiz attempt
        (new XpService())->awardXp($this->student(), 30, 'quiz', 'Completed quiz assessment', $attempt->id);

        // Update streak — taking a quiz counts as study activity
        (new StudentStreakService())->updateStreak($this->student());

        // Evaluate and award any eligible badges
        (new BadgeService())->checkAndAwardBadges($this->student());

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

        if (!$attempt) {
            return response()->json([
                'success' => true,
                'message' => 'No quiz attempt found',
                'data' => null
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Quiz result fetched successfully',
            'data' => $this->attemptResult($attempt)
        ], 200);
    }



    public function flashcards(Chapter $chapter)
    {
        if (!$this->canAccessClass($chapter->class_id)) {
            return $this->unauthorized();
        }

        $questions = $chapter->quizQuestions()
            ->orderBy('display_order')
            ->get();

        if ($questions->isEmpty()) {
            return $this->notFound('No flashcards found for this chapter');
        }

        return response()->json([
            'success' => true,
            'message' => 'Flashcards fetched successfully',
            'data' => FlashcardResource::collection($questions)
        ], 200);
    }

    public function moduleQuiz(TopicNote $note)
    {
        if (!$this->canAccessClass($note->class_id)) {
            return $this->unauthorized();
        }

        $questions = $note->quizQuestions()
            ->orderBy('display_order')
            ->get();

        if ($questions->isEmpty()) {
            return $this->notFound('No quiz questions found for this module');
        }

        return response()->json([
            'success' => true,
            'message' => 'Module quiz fetched successfully',
            'data' => [
                'id' => $note->id,
                'title' => 'Quiz for ' . $note->file_url,
                'passing_marks_percentage' => 60,
                'questions' => $questions
            ]
        ], 200);
    }

    public function moduleFlashcards(TopicNote $note)
    {
        if (!$this->canAccessClass($note->class_id)) {
            return $this->unauthorized();
        }

        $questions = $note->quizQuestions()
            ->orderBy('display_order')
            ->get();

        if ($questions->isEmpty()) {
            return $this->notFound('No flashcards found for this module');
        }

        return response()->json([
            'success' => true,
            'message' => 'Module flashcards fetched successfully',
            'data' => FlashcardResource::collection($questions)
        ], 200);
    }

    public function v2Chapters($classId, $subjectId)
    {
        $class = ClassModel::find($classId);
        if (!$class || !$this->canAccessClass($class->id)) return $this->notFound('Class not found');

        $chapters = Chapter::where('class_id', $class->id)
            ->where('subject_id', $subjectId)
            ->where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('title')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Chapters fetched successfully',
            'data' => \App\Http\Resources\ChapterResource::collection($chapters)
        ], 200);
    }

    public function v2TopicNotes(Chapter $chapter)
    {
        if (!$this->canAccessClass($chapter->class_id)) return $this->unauthorized();

        $notes = $chapter->topicNotes()->orderBy('id')->get();

        return response()->json([
            'success' => true,
            'message' => 'Topic Notes fetched successfully',
            'data' => \App\Http\Resources\TopicNoteResource::collection($notes)
        ], 200);
    }

    public function v2ChapterQuestionBank(Chapter $chapter)
    {
        if (!$this->canAccessClass($chapter->class_id)) return $this->unauthorized();

        $quiz = $chapter->quiz()
            ->whereNull('topic_note_id')
            ->with(['questions' => function ($query) { $query->orderBy('display_order'); }])
            ->first();

        if (!$quiz) return $this->notFound('Question Bank not found for this Chapter');

        return response()->json([
            'success' => true,
            'message' => 'Chapter Question Bank fetched successfully',
            'data' => new \App\Http\Resources\QuestionBankResource($quiz)
        ], 200);
    }

    public function v2TopicNoteQuestionBank(Note $topic_note)
    {
        if (!$this->canAccessClass($topic_note->class_id)) return $this->unauthorized();

        $quiz = $topic_note->quiz()
            ->with(['questions' => function ($query) { $query->orderBy('display_order'); }])
            ->first();

        if (!$quiz) return $this->notFound('Question Bank not found for this Topic Note');

        return response()->json([
            'success' => true,
            'message' => 'Topic Note Question Bank fetched successfully',
            'data' => new \App\Http\Resources\QuestionBankResource($quiz)
        ], 200);
    }

    private function topicProgressData(?Chapter $chapter, Student $student): ?array
    {
        if (!$chapter) {
            return null;
        }

        $noteIds = $chapter->topicNotes()->pluck('id');
        $totalNotes = $noteIds->count();
        $completedNotes = StudentNoteProgress::where('student_id', $student->id)
            ->whereIn('topic_note_id', $noteIds)
            ->count();

        return [
            'chapter_id' => $chapter->id,
            'total_notes' => $totalNotes,
            'completed_notes' => $completedNotes,
            'is_completed' => $totalNotes > 0 && $completedNotes === $totalNotes,
            'quiz_unlocked' => $totalNotes > 0 && $completedNotes === $totalNotes,
        ];
    }

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

    private function student(): Student
    {
        return Student::where('user_id', auth()->id())->firstOrFail();
    }

    private function approvedClassIds()
    {
        return Enrollment::where('user_id', auth()->id())
            ->where('status', 'approved')
            ->pluck('class_id');
    }

    private function canAccessClass($classId): bool
    {
        return Enrollment::where('user_id', auth()->id())
            ->where('class_id', $classId)
            ->where('status', 'approved')
            ->exists();
    }

    private function unauthorized()
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access'
        ], 403);
    }

    private function notFound($message)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], 404);
    }

    public function myDoubts(Request $request)
    {
        $query = Doubt::where('user_id', auth()->id())->with(['class', 'subject', 'chapter', 'answeredBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'message' => 'My doubts fetched successfully',
            'data' => $query->latest()->get()
        ], 200);
    }
}
