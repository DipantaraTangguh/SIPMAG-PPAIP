<?php

namespace App\Http\Requests\Defense;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleDefenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scheduled_date' => 'required|date|after:today',
            'scheduled_time' => 'nullable|string|max:10',
            'room' => 'nullable|string|max:100',
            'dosen_penguji_1' => 'required|string|max:255',
            'dosen_penguji_2' => 'required|string|max:255',
        ];
    }
}
