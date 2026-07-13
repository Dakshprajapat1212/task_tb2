<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'subject_id',
        'title',
        'description',
        'display_order',
        'status'
    ];

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function topicNotes()
    {
        return $this->hasMany(TopicNote::class, 'chapter_id');
    }

    public function quizQuestions()
    {
        return $this->hasMany(QuizQuestion::class, 'chapter_id');
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class, 'chapter_id');
    }
}
