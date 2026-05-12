<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'student_id',
        'internship_id',
        'cv_file_path',
        'loa_path',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }
}
