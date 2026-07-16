<?php

namespace App\Services;

use App\Models\Doubt;
use Illuminate\Support\Facades\DB;

class DoubtService
{
    /**
     * Perform an intelligent search across quiz_questions and resolved doubts.
     * Uses MySQL FULLTEXT MATCH...AGAINST for relevance scoring.
     */
    public function searchIntelligent(int $chapterId, string $query)
    {
        // 1. Search Quiz Questions
        $quizResults = DB::table('quiz_questions')
            ->selectRaw("id, question, correct_answer as answer, explanation, MATCH(question) AGAINST(? IN BOOLEAN MODE) as relevance", [$query])
            ->where('chapter_id', $chapterId)
            ->whereRaw("MATCH(question) AGAINST(? IN BOOLEAN MODE)", [$query])
            ->having('relevance', '>', 0)
            ->get()
            ->map(function ($item) {
                $item->source = 'question_bank';
                return $item;
            });

        // 2. Search Resolved Doubts
        $doubtResults = DB::table('doubts')
            ->selectRaw("id, question, answer, explanation, MATCH(question) AGAINST(? IN BOOLEAN MODE) as relevance", [$query])
            ->where('chapter_id', $chapterId)
            ->where('status', 'resolved')
            ->whereRaw("MATCH(question) AGAINST(? IN BOOLEAN MODE)", [$query])
            ->having('relevance', '>', 0)
            ->get()
            ->map(function ($item) {
                $item->source = 'resolved_doubt';
                return $item;
            });

        // 3. Merge and Sort by Relevance
        $merged = $quizResults->merge($doubtResults)->sortByDesc('relevance')->values();

        // 4. Fallback to Fuzzy Search (Levenshtein) if no exact FULLTEXT matches found
        if ($merged->isEmpty()) {
            return $this->fuzzySearch($chapterId, $query);
        }

        return $merged;
    }

    /**
     * Fallback fuzzy search using PHP's levenshtein distance.
     * Perfect for catching typos like "polymorpism" instead of "polymorphism".
     */
    private function fuzzySearch(int $chapterId, string $query)
    {
        $quizQuestions = DB::table('quiz_questions')
            ->select('id', 'question', 'correct_answer as answer', 'explanation')
            ->where('chapter_id', $chapterId)
            ->get()
            ->map(function ($item) {
                $item->source = 'question_bank';
                return $item;
            });

        $resolvedDoubts = DB::table('doubts')
            ->select('id', 'question', 'answer', 'explanation')
            ->where('chapter_id', $chapterId)
            ->where('status', 'resolved')
            ->get()
            ->map(function ($item) {
                $item->source = 'resolved_doubt';
                return $item;
            });

        $all = $quizQuestions->merge($resolvedDoubts);

        // Extract meaningful words from the query (> 3 chars to ignore 'the', 'is', 'how')
        $queryWords = array_filter(explode(' ', strtolower(preg_replace('/[^a-z0-9 ]/', '', $query))), fn($w) => strlen($w) > 3);
        if (empty($queryWords)) {
            $queryWords = [strtolower(preg_replace('/[^a-z0-9 ]/', '', $query))];
        }

        $results = [];

        foreach ($all as $item) {
            $questionWords = explode(' ', strtolower(preg_replace('/[^a-z0-9 ]/', '', $item->question)));
            $score = 0;

            foreach ($queryWords as $qw) {
                $bestMatch = 999;
                foreach ($questionWords as $qw2) {
                    $lev = levenshtein($qw, $qw2);
                    if ($lev < $bestMatch) {
                        $bestMatch = $lev;
                    }
                }
                
                // Scoring system based on typos
                if ($bestMatch === 0) $score += 10;       // Exact match
                elseif ($bestMatch === 1) $score += 5;    // 1 typo
                elseif ($bestMatch === 2) $score += 2;    // 2 typos
            }

            // Only include results that have at least some fuzzy match
            if ($score > 0) {
                $item->relevance = $score;
                $results[] = $item;
            }
        }

        // Sort by fuzzy relevance score descending
        usort($results, fn($a, $b) => $b->relevance <=> $a->relevance);

        return collect(array_slice($results, 0, 10)); // Return top 10 fuzzy matches
    }

    /**
     * Submit a new doubt, checking for exact duplicates first.
     */
    public function submitDoubt(int $userId, int $classId, int $subjectId, int $chapterId, string $question)
    {
        // Prevent spam: Check if this user recently submitted the exact same question for this chapter
        $existing = Doubt::where('user_id', $userId)
            ->where('chapter_id', $chapterId)
            ->where('question', $question)
            ->first();

        if ($existing) {
            return $existing; // Return existing instead of duplicating
        }

        return Doubt::create([
            'user_id' => $userId,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'chapter_id' => $chapterId,
            'question' => $question,
            'status' => 'pending'
        ]);
    }

    /**
     * Answer a pending doubt.
     */
    public function answerDoubt(int $doubtId, int $facultyId, string $answer, ?string $explanation)
    {
        $doubt = Doubt::findOrFail($doubtId);
        
        $doubt->update([
            'answer' => $answer,
            'explanation' => $explanation,
            'status' => 'resolved',
            'answered_by' => $facultyId
        ]);

        return $doubt;
    }
}
