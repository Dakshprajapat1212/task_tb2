<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subjects', 'class_id', 'subject_id')
                    ->withPivot('id', 'faculty_id', 'class_link', 'class_date', 'start_time', 'end_time', 'stream_url')
                    ->withTimestamps();
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'class_id');
    }

    /**
     * Scope a query to only include classes assigned to a specific faculty.
     */
    public function scopeForFaculty($query, $facultyId)
    {
        return $query->whereHas('subjects', function($q) use ($facultyId) {
            $q->where('class_subjects.faculty_id', $facultyId);
        });
    }
}
