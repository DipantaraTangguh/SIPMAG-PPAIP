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
            'no_telepon' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mulai_magang' => 'required|date',
            'selesai_magang' => 'required|date|after_or_equal:mulai_magang',
            'loa_file' => 'required|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:5120',
        ];
    }
}
