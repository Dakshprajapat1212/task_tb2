<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doubt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'class_id',
        'subject_id',
        'chapter_id',
        'question',
        'status',
        'answer',
        'explanation',
        'answered_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    public function answeredBy()
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}
