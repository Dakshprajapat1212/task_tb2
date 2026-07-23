<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBadge extends Model
{
    use HasFactory;

    protected $table = 'student_badges';

    protected $fillable = [
        'student_id',
        'badge_id',
        'title',
        'unlocked_at'
    ];

    protected $casts = [
        'unlocked_at' => 'datetime'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
