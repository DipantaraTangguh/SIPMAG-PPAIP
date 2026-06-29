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
        'status',
        'scheduled_date',
        'scheduled_time',
        'room',
        'dosen_penguji_1_id',
        'dosen_penguji_2_id',
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

    public function examinerOne()
    {
        return $this->belongsTo(Lecturer::class, 'dosen_penguji_1_id');
    }

    public function examinerTwo()
    {
        return $this->belongsTo(Lecturer::class, 'dosen_penguji_2_id');
    }

    public function assessments()
    {
        return $this->hasMany(DefenseAssessment::class);
    }
}
