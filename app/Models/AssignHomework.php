<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignHomework extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | TABLE NAME
    |--------------------------------------------------------------------------
    */

    protected $table = 'assign_homeworks';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'class_id',
        'subject_id',
        'topic',
        'description',
        'due_date',
        'status',
        'points',
        'xp'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function submissions()
    {
        return $this->hasMany(SubmitHomework::class, 'assign_homework_id');
    }

    public function issues()
    {
        return $this->hasMany(HomeworkIssue::class, 'assign_homework_id');
    }
}
