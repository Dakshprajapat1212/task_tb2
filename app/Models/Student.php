<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    protected $table = 'students';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'user_id',
        'xp',
        'streak_days',
        'last_activity_date',
        'dob',
        'address',
        'full_name',
        'email',
        'mobile',
        'gender',
        'photo',
        'school',
        'board',
        'course',
        'batch_mode',
        'father_name',
        'father_occupation',
        'mother_name',
        'parent_mobile',
        'marksheet'
    ];

    /**
     * Cast last_activity_date to a Carbon date instance so ->toDateString() works cleanly.
     */
    protected $casts = [
        'last_activity_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Student belongs to user
    public function user()
    {
        return $this->belongsTo(

            User::class,

            'user_id'
        );
    }

    public function submissions()
    {
        return $this->hasMany(SubmitHomework::class, 'student_id');
    }

    public function noteProgress()
    {
        return $this->hasMany(StudentNoteProgress::class, 'student_id');
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class, 'student_id');
    }

    public function badges()
    {
        return $this->hasMany(StudentBadge::class, 'student_id');
    }

    public function xpLogs()
    {
        return $this->hasMany(XpLog::class, 'student_id');
    }
}
