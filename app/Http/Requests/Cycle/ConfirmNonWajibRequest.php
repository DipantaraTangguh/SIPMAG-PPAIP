<?php

namespace App\Http\Requests\Cycle;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmNonWajibRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // hasil=diterima wajib bawa bukti; hasil=ditolak cukup status saja.
        return [
            'hasil' => 'required|string|in:diterima,ditolak',
            'company_name' => 'required_if:hasil,diterima|nullable|string|max:255',
            'alamat_perusahaan' => 'nullable|string|max:2000',
            'tanggal_mulai' => 'required_if:hasil,diterima|nullable|date_format:Y-m',
            'tanggal_selesai' => 'required_if:hasil,diterima|nullable|date_format:Y-m|after_or_equal:tanggal_mulai',
            'loa_file' => 'required_if:hasil,diterima|nullable|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:5120',
        ];
    }
}
