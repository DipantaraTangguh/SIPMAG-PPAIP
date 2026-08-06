<?php

namespace App\Http\Requests\Internships;

use App\Support\StudyProgram;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInternshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => 'sometimes|string|max:255',
            'position' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'capacity' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'study_programs' => 'nullable|array',
            'study_programs.*' => ['string', Rule::in(StudyProgram::ALL)],
            'start_date' => 'nullable|date',
            'job_description' => 'nullable|array',
            'skills' => 'nullable|array',
            'requirements' => 'nullable|array',
            'minimum_education' => 'nullable|string',
            'sistem_kerja' => 'nullable|string',
            'location' => 'nullable|string',
            'deadline' => 'sometimes|date',
            'is_active' => 'boolean',
        ];
    }
}
