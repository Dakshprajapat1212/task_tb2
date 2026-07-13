<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentNoteProgress extends Model
{
    use HasFactory;

    protected $table = 'student_topic_note_progress';

    protected $fillable = [
        'student_id',
        'topic_note_id',
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function topicNote()
    {
        return $this->belongsTo(TopicNote::class, 'topic_note_id');
    }
}
