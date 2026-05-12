<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupervisorApplication extends Model
{
    protected $fillable = [
        'student_id',
        'company_name',
        'company_contact',
        'loa_path',
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
