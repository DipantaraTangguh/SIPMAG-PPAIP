<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    protected $fillable = [
        'user_id',
        'nidn',
        'lecturer_name',
        'contact',
        'study_program',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Students supervised by this DPM */
    public function supervisedStudents()
    {
        return $this->hasMany(Student::class, 'dpm_id');
    }
}
