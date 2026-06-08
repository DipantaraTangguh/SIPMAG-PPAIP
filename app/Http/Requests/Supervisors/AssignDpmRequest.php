<?php

namespace App\Http\Requests\Supervisors;

use Illuminate\Foundation\Http\FormRequest;

class AssignDpmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|integer|exists:students,id',
            'lecturer_id' => 'required|integer|exists:lecturers,id',
        ];
    }
}
