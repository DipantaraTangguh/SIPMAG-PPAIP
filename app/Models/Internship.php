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
        'bidang',
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
            'start_date' => 'date',
            'deadline' => 'date',
            'is_active' => 'boolean',
        ];
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
