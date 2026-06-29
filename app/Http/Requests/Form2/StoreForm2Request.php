<?php

namespace App\Http\Requests\Form2;

use Illuminate\Foundation\Http\FormRequest;

class StoreForm2Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'nama_pimpinan' => 'nullable|string|max:255',
            'jabatan_pimpinan' => 'nullable|string|max:255',
            'alamat_perusahaan' => 'required|string|max:2000',
            'lingkup_magang' => 'required|string|max:2000',
            'tanggal_mulai' => 'required|date_format:Y-m',
            'tanggal_selesai' => 'required|date_format:Y-m|after_or_equal:tanggal_mulai',
        ];
    }
}
