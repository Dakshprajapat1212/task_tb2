<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkIssue extends Model
{
    use HasFactory;

    protected $table = 'homework_issues';

    protected $fillable = [
        'assign_homework_id',
        'student_id',
        'issue_type',
        'description',
        'status'
    ];

    public function assignHomework()
    {
        return $this->belongsTo(AssignHomework::class, 'assign_homework_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
