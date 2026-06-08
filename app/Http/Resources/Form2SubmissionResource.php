<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Form2SubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'company_name' => $this->company_name,
            'alamat_perusahaan' => $this->alamat_perusahaan,
            'lingkup_magang' => $this->lingkup_magang,
            'tanggal_mulai' => $this->tanggal_mulai?->toDateString(),
            'tanggal_selesai' => $this->tanggal_selesai?->toDateString(),
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'pdf_path' => $this->pdf_path,
            'submitted_at' => $this->submitted_at?->toJSON(),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
            'student' => StudentResource::make($this->whenLoaded('student')),
        ];
    }
}
