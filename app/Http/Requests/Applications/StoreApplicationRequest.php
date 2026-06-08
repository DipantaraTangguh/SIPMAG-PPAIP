<?php

namespace App\Http\Requests\Applications;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'internship_id' => 'required|integer|exists:internships,id',
            'cv_file' => 'required|file|mimes:pdf|mimetypes:application/pdf|max:5120',
        ];
    }
}
