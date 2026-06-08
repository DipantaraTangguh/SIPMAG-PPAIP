<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LecturerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nidn' => $this->nidn,
            'name' => $this->lecturer_name,
            'lecturer_name' => $this->lecturer_name,
            'contact' => $this->contact,
            'study_program' => $this->study_program,
        ];
    }
}
