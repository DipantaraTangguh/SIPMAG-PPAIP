<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form2Submission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'company_name',
        'nama_pimpinan',
        'jabatan_pimpinan',
        'alamat_perusahaan',
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
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'submitted_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
