<?php

namespace App\Http\Requests\Defense;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'dosen_penguji_1_id' => 'required|integer|exists:lecturers,id',
            'dosen_penguji_2_id' => 'required|integer|exists:lecturers,id|different:dosen_penguji_1_id',
        ];
    }

    /**
     * @return array<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $dpmId = Student::query()
                    ->whereKey((int) $this->route('studentId'))
                    ->value('dpm_id');

                if (! $dpmId) {
                    return;
                }

                foreach (['dosen_penguji_1_id', 'dosen_penguji_2_id'] as $field) {
                    if ((int) $this->input($field) === (int) $dpmId) {
                        $validator->errors()->add(
                            $field,
                            'Dosen pembimbing tidak dapat dipilih sebagai dosen penguji pada sidang yang sama.',
                        );
                    }
                }
            },
        ];
    }
}
