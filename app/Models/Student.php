<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'dpm_id',
        'nim',
        'name',
        'study_program',
        'email',
        'access_status',
        'is_independent',
        'form1_data',
        'form1_pdf_path',
        'form1_rejection_reason',
        'approved_logbook_count',
    ];

    protected function casts(): array
    {
        return [
            'form1_data'     => 'array',
            'is_independent' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dpm()
    {
        return $this->belongsTo(Lecturer::class, 'dpm_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function form2Submissions()
    {
        return $this->hasMany(Form2Submission::class);
    }

    public function logbooks()
    {
        return $this->hasMany(Logbook::class);
    }

    public function sidangSubmission()
    {
        return $this->hasOne(SidangSubmission::class);
    }

    public function supervisorApplication()
    {
        return $this->hasOne(SupervisorApplication::class);
    }
}
