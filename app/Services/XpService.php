<?php

namespace App\Services;

use App\Models\Student;
use App\Models\XpLog;
use Illuminate\Support\Facades\DB;

class XpService
{
    /**
     * Atomically awards XP to a student and creates an itemized audit log entry.
     *
     * @param Student $student Target student model
     * @param int $amount XP amount to increment (e.g. +20, +30, +50)
     * @param string $source Category source ('note', 'quiz', 'homework', 'streak', 'live_class')
     * @param string $description Human-readable explanation of why XP was awarded
     * @param int|null $referenceId Optional reference ID to entity (note_id, attempt_id, submission_id)
     * @return XpLog Created log record
     */
    public function awardXp(
        Student $student,
        int $amount,
        string $source,
        string $description,
        ?int $referenceId = null
    ): XpLog {
        return DB::transaction(function () use ($student, $amount, $source, $description, $referenceId) {
            // 1. Increment total XP on students table
            $student->increment('xp', $amount);

            // 2. Create itemized audit log entry
            return XpLog::create([
                'student_id'   => $student->id,
                'amount'       => $amount,
                'source'       => $source,
                'description'  => $description,
                'reference_id' => $referenceId,
            ]);
        });
    }
}
