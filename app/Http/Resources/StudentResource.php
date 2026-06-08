<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dpm_id' => $this->dpm_id,
            'nim' => $this->nim,
            'name' => $this->name,
            'study_program' => $this->study_program,
            'email' => $this->email,
            'semester' => $this->semester,
            'tahun_akademik' => $this->tahun_akademik,
            'jumlah_sks' => $this->jumlah_sks,
            'ipk' => $this->ipk,
            'access_status' => $this->access_status,
            'is_independent' => (bool) $this->is_independent,
            'approved_logbook_count' => $this->approved_logbook_count,
            'form1_data' => $this->when($this->hasAttribute('form1_data'), $this->form1_data),
            'form1_pdf_path' => $this->when($this->hasAttribute('form1_pdf_path'), $this->form1_pdf_path),
            'form1_rejection_reason' => $this->when($this->hasAttribute('form1_rejection_reason'), $this->form1_rejection_reason),
            'updated_at' => $this->updated_at?->toJSON(),
            'dpm' => LecturerResource::make($this->whenLoaded('dpm')),
            'sidang_submission' => DefenseSubmissionResource::make($this->whenLoaded('sidangSubmission')),
        ];
    }

    private function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->resource->getAttributes());
    }
}
