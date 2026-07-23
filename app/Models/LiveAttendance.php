<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveAttendance extends Model
{
    use HasFactory;

    protected $table = 'live_attendances';

    protected $fillable = [
        'student_id',
        'class_id',
        'subject_id',
        'class_date',
        'joined_at',
        'completed_at',
        'duration_seconds'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'completed_at' => 'datetime',
        'class_date' => 'date'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
