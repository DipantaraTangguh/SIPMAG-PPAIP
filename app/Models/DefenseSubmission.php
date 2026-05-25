<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefenseSubmission extends Model
{
    protected $table = 'sidang_submissions';

    protected $fillable = [
        'student_id',
        'laporan_path',
        'poster_path',
        'krs_path',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
