<?php

namespace App\Http\Requests\Form1;

use Illuminate\Foundation\Http\FormRequest;

class RejectForm1Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:500',
        ];
    }
}
