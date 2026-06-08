<?php

namespace App\Http\Requests\Logbooks;

class UpdateLogbookRequest extends StoreLogbookRequest
{
    public function rules(): array
    {
        $student = $this->user()->student;

        return [
            'tanggal' => $this->dateRules($student, (int) $this->route('id'), false),
            'kegiatan_harian' => 'sometimes|string',
            'hasil' => 'sometimes|string',
        ];
    }
}
