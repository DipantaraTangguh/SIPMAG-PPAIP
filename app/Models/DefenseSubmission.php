<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DefenseSubmission extends Model
{
    use SoftDeletes;
    protected $table = 'sidang_submissions';

    protected $fillable = [
        'student_id',
        'laporan_path',
        'poster_path',
        'foto_kegiatan_1_path',
        'foto_kegiatan_2_path',
        'krs_path',
        'status',
        'scheduled_date',
        'scheduled_time',
        'room',
        'dosen_penguji_1',
        'dosen_penguji_2',
        'scheduled_by',
        'scheduled_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'scheduled_date' => 'date',
            'scheduled_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scheduler()
    {
        return $this->belongsTo(Lecturer::class, 'scheduled_by');
    }
}
