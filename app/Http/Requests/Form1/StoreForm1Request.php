<?php

namespace App\Http\Requests\Form1;

use Illuminate\Foundation\Http\FormRequest;

class StoreForm1Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'skemaMagang' => 'required|string|in:Mitra,Mandiri,Kewirausahaan',
            'topikMagang' => 'required|string|max:2000',
            'outputTarget' => 'required|string|in:Produk,Prototype,Laporan',
            'transkrip' => 'required|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:5120',
        ];
    }
}
