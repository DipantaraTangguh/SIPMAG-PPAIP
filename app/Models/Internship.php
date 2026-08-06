<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    protected $fillable = [
        'company_name',
        'position',
        'description',
        'capacity',
        'duration',
        'study_programs',
        'start_date',
        'job_description',
        'skills',
        'requirements',
        'minimum_education',
        'sistem_kerja',
        'location',
        'deadline',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'job_description' => 'array',
            'skills' => 'array',
            'requirements' => 'array',
            'study_programs' => 'array',
            'start_date' => 'date',
            'deadline' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Lowongan tanpa daftar prodi dianggap terbuka untuk semua prodi --
     * termasuk lowongan lama yang bidang-nya belum dipetakan PPAIP.
     */
    public function acceptsStudyProgram(?string $studyProgram): bool
    {
        $allowed = $this->study_programs;

        if (empty($allowed)) {
            return true;
        }

        return $studyProgram !== null && in_array($studyProgram, $allowed, true);
    }

    public function scopeOpenForApplications(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereDate('deadline', '>=', today());
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
