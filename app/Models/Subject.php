<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Subject belongs to one faculty
    public function faculty()
    {
    }

    // Subject belongs to many classes
    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_subjects')
                    ->withTimestamps();
    }

    // Subject has many notes
    public function topicNotes()
    {
        return $this->hasMany(TopicNote::class, 'subject_id');
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'subject_id');
    }
}
