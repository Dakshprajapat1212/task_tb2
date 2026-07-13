<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopicNote extends Model
{
    use HasFactory;

    protected $table = 'topic_notes';

    protected $fillable = [

        'class_id',

        'subject_id',

        'chapter_id',

        'chapter',

        'file_url'
    ];

    /*
    |--------------------------------------------------------------------------
    | NOTE BELONGS TO CLASS
    |--------------------------------------------------------------------------
    */

    public function class()
    {
        return $this->belongsTo(

            ClassModel::class,

            'class_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NOTE BELONGS TO SUBJECT
    |--------------------------------------------------------------------------
    */

    public function subject()
    {
        return $this->belongsTo(

            Subject::class,

            'subject_id'
        );
    }

    public function libraryTopic()
    {
        return $this->belongsTo(

            Chapter::class,

            'chapter_id'
        );
    }

    public function progress()
    {
        return $this->hasMany(

            StudentNoteProgress::class,

            'topic_note_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NOTE HAS MANY QUIZ QUESTIONS
    |--------------------------------------------------------------------------
    */
    public function quizQuestions()
    {
        return $this->hasMany(QuizQuestion::class, 'topic_note_id');
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class, 'topic_note_id');
    }
}
