<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_of_joining',
        'qualification',
        'bio'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Faculty belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Faculty has many subjects
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'faculty_id');
    }

    // Faculty has many classes
    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'faculty_id');
    }
}
