<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XpLog extends Model
{
    use HasFactory;

    protected $table = 'xp_logs';

    protected $fillable = [
        'student_id',
        'amount',
        'source',
        'description',
        'reference_id'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
