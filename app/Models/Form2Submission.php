<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form2Submission extends Model
{
    protected $fillable = [
        'student_id',
        'company_name',
        'contact_person_name',
        'contact_person_role',
        'contact_info',
        'lingkup_magang',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'rejection_reason',
        'pdf_path',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai'   => 'date',
            'tanggal_selesai' => 'date',
            'submitted_at'    => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
