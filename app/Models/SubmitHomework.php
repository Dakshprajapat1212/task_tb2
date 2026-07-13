<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmitHomework extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | TABLE NAME
    |--------------------------------------------------------------------------
    */

    protected $table = 'submit_homeworks';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'assign_homework_id',

        'student_id',

        'file',

        'status',

        'remarks'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP -> ASSIGNED HOMEWORK
    |--------------------------------------------------------------------------
    */

    public function assignHomework()
    {
        return $this->belongsTo(

            AssignHomework::class,

            'assign_homework_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP -> STUDENT
    |--------------------------------------------------------------------------
    */

    public function student()
    {
        return $this->belongsTo(

            Student::class,

            'student_id'
        );
    }
}