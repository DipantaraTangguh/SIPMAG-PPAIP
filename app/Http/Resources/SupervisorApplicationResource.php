<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupervisorApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'company_name' => $this->company_name,
            'company_contact' => $this->company_contact,
            'nama_praktisi' => $this->nama_praktisi,
            'jabatan_praktisi' => $this->jabatan_praktisi,
            'no_telepon' => $this->no_telepon,
            'email' => $this->email,
            'mulai_magang' => $this->mulai_magang?->toDateString(),
            'selesai_magang' => $this->selesai_magang?->toDateString(),
            'loa_path' => $this->loa_path,
            'submitted_at' => $this->submitted_at?->toJSON(),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
            'student' => StudentResource::make($this->whenLoaded('student')),
        ];
    }
}
