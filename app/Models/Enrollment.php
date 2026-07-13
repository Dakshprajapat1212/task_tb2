<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    protected $table = 'enrollments';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'user_id',

        'class_id',

        'dob',

        'address',

        'status'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Enrollment belongs to user
    public function user()
    {
        return $this->belongsTo(

            User::class,

            'user_id'
        );
    }

    // Enrollment belongs to class
    public function class()
    {
        return $this->belongsTo(

            ClassModel::class,

            'class_id'
        );
    }
}