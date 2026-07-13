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

        'topic',

        'description',

        'due_date',

        'status'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP -> CLASS
    |--------------------------------------------------------------------------
    */

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function submissions()
    {
        return $this->hasMany(SubmitHomework::class, 'assign_homework_id');
    }
}
