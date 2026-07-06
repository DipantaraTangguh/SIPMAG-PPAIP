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
            'skemaMagang'  => 'required|string|in:Magang Perusahaan,Magang Kewirausahaan',
            'topikMagang'  => 'required|string|max:2000',
            'outputTarget' => 'required|string|in:Produk,Prototype,Laporan',
        ];
    }
}
