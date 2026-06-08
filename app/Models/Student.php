<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'dpm_id',
        'nim',
        'name',
        'study_program',
        'email',
        'semester',
        'tahun_akademik',
        'jumlah_sks',
        'ipk',
        'is_independent',
        'form1_data',
        'form1_pdf_path',
        'form1_rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'form1_data' => 'array',
            'is_independent' => 'boolean',
            'form1_approved_at' => 'datetime',
            'semester' => 'integer',
            'jumlah_sks' => 'integer',
            'ipk' => 'float',
        ];
    }

    public function getTahunAkademikAttribute(): string
    {
        $now = now();
        $year = $now->year;
        $month = $now->month;

        if ($month < 8) {
            return ($year - 1).'/'.$year;
        }

        return $year.'/'.($year + 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dpm()
    {
        return $this->belongsTo(Lecturer::class, 'dpm_id');
    }

    public function form1Approver()
    {
        return $this->belongsTo(Lecturer::class, 'form1_approved_by');
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

    public function getApprovedLogbookCountAttribute(int|string|null $value): int
    {
        return (int) ($value ?? $this->logbooks()->where('status', 'Approved')->count());
    }

    public function sidangSubmission()
    {
        return $this->hasOne(DefenseSubmission::class);
    }

    public function supervisorApplication()
    {
        return $this->hasOne(SupervisorApplication::class);
    }
}
