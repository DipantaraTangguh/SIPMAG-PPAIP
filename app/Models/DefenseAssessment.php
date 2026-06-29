<?php

namespace App\Models;

use App\Enums\AssessorRole;
use Illuminate\Database\Eloquent\Model;

class DefenseAssessment extends Model
{
    protected $fillable = [
        'defense_submission_id',
        'lecturer_id',
        'assessor_role',
        'internship_performance_score',
        'final_report_score',
        'presentation_score',
    ];

    protected function casts(): array
    {
        return [
            'assessor_role' => AssessorRole::class,
            'internship_performance_score' => 'decimal:2',
            'final_report_score' => 'decimal:2',
            'presentation_score' => 'decimal:2',
        ];
    }

    public function defenseSubmission()
    {
        return $this->belongsTo(DefenseSubmission::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }
}
