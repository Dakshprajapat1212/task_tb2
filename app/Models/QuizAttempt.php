<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'chapter_id',
        'topic_note_id',
        'score',
        'total_questions',
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime'
    ];

    /**
     * Computed accessor: score as a percentage (0–100).
     * Allows ->sum('score_percentage') and ->avg('score_percentage')
     * to work correctly on Eloquent collections.
     *
     * Example: score=3, total_questions=5 → score_percentage = 60.0
     */
    public function getScorePercentageAttribute(): float
    {
        if (!$this->total_questions || $this->total_questions == 0) {
            return 0.0;
        }
        return round(($this->score / $this->total_questions) * 100, 2);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    public function topicNote()
    {
        return $this->belongsTo(TopicNote::class, 'topic_note_id');
    }

    public function answers()
    {
        return $this->hasMany(QuizAnswer::class, 'attempt_id');
    }
}
