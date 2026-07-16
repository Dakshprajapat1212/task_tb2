<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    use HasFactory;

    protected $table = 'recordings';

    protected $fillable = [
        'class_id',
        'subject_id',
        'chapter_id',
        'topic',
        'duration',
        'video_link',
        'teacher_name',
        'chapters'
    ];

    protected $casts = [
        'chapters' => 'array'
    ];

    /*
    |--------------------------------------------------------------------------
    | RECORDING BELONGS TO CLASS / SUBJECT / CHAPTER
    |--------------------------------------------------------------------------
    */

    public function class()
    {
        return $this->belongsTo(
            ClassModel::class,
            'class_id'
        );
    }

    public function subject()
    {
        return $this->belongsTo(
            Subject::class,
            'subject_id'
        );
    }

    public function chapter()
    {
        return $this->belongsTo(
            Chapter::class,
            'chapter_id'
        );
    }
}