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
            'alamat_perusahaan' => 'required|string',
            'lingkup_magang' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ];
    }
}
