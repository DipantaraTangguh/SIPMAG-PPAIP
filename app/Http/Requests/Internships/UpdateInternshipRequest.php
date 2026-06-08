<?php

namespace App\Http\Requests\Internships;

use Illuminate\Foundation\Http\FormRequest;

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
            'bidang' => 'nullable|string|max:255',
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
