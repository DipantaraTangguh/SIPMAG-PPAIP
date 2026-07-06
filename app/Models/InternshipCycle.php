<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Riwayat satu siklus magang yang telah selesai (wajib atau non-wajib).
 * Append-only: tidak pernah dihapus saat mahasiswa mereset siklusnya.
 */
class InternshipCycle extends Model
{
    protected $fillable = [
        'student_id',
        'cycle_number',
        'jenis_magang',
        'outcome_status',
        'nim',
        'nama',
        'study_program',
        'semester',
        'ipk',
        'skema_magang',
        'topik_magang',
        'output_target',
        'company_name',
        'alamat_perusahaan',
        'nama_pimpinan',
        'tanggal_mulai',
        'tanggal_selesai',
        'final_score',
        'letter_grade',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'cycle_number' => 'integer',
            'semester' => 'integer',
            'ipk' => 'float',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'final_score' => 'float',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
