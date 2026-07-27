<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentStudyLog extends Model
{
    use HasFactory;

    protected $table = 'student_study_logs';

    protected $fillable = [
        'student_id',
        'date',
        'duration_seconds',
        'activity_type'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
