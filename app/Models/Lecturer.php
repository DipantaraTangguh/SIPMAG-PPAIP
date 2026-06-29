<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    protected $fillable = [
        'user_id',
        'nidn',
        'lecturer_name',
        'contact',
        'study_program',
        'signature_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supervisedStudents()
    {
        return $this->hasMany(Student::class, 'dpm_id');
    }

    public function defenseAssessments()
    {
        return $this->hasMany(DefenseAssessment::class);
    }

    public function scopeEligibleDpmForStudyProgram(Builder $query, string $studyProgram): Builder
    {
        return $query
            ->where('study_program', $studyProgram)
            ->whereHas('user', fn (Builder $userQuery) => $userQuery->where('role', 'dpm'));
    }
}
