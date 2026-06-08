<?php

namespace App\Http\Requests\Logbooks;

use Illuminate\Foundation\Http\FormRequest;

class RejectLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => 'nullable|string|max:500',
        ];
    }
}
