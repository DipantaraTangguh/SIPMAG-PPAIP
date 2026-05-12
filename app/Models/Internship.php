<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    protected $fillable = [
        'company_name',
        'position',
        'description',
        'vacancy_details',
        'job_description',
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
            'deadline'        => 'date',
            'is_active'       => 'boolean',
        ];
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
