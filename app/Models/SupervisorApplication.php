<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupervisorApplication extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'student_id',
        'company_name',
        'company_contact',
        'nama_praktisi',
        'jabatan_praktisi',
        'no_telepon',
        'email',
        'mulai_magang',
        'selesai_magang',
        'loa_path',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at'   => 'datetime',
            'mulai_magang'   => 'date',
            'selesai_magang' => 'date',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
