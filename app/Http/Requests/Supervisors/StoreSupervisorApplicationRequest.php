<?php

namespace App\Http\Requests\Supervisors;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupervisorApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'company_contact' => 'required|string|max:255',
            'nama_praktisi' => 'required|string|max:255',
            'jabatan_praktisi' => 'required|string|max:255',
            'no_telepon' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9][0-9\s().-]{7,19}$/'],
            'email' => 'required|email:rfc|max:255',
            'mulai_magang' => 'required|date',
            'selesai_magang' => 'required|date|after:mulai_magang',
            'loa_file' => 'required|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:5120',
        ];
    }
}
