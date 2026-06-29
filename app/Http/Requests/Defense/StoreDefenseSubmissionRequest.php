<?php

namespace App\Http\Requests\Defense;

use Illuminate\Foundation\Http\FormRequest;

class StoreDefenseSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'laporan' => 'required|file|mimes:pdf|mimetypes:application/pdf|max:10240',
            'poster' => 'required|file|mimes:pdf|mimetypes:application/pdf|max:5120',
            'foto_kegiatan_1' => 'required|file|mimes:jpg,jpeg,png,pdf|mimetypes:image/jpeg,image/png,application/pdf|max:5120',
            'foto_kegiatan_2' => 'required|file|mimes:jpg,jpeg,png,pdf|mimetypes:image/jpeg,image/png,application/pdf|max:5120',
        ];
    }
}
