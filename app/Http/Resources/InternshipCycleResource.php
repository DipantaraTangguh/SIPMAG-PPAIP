<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InternshipCycleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cycle_number' => $this->cycle_number,
            'jenis_magang' => $this->jenis_magang,
            'outcome_status' => $this->outcome_status,
            'nim' => $this->nim,
            'nama' => $this->nama,
            'study_program' => $this->study_program,
            'semester' => $this->semester,
            'ipk' => $this->ipk,
            'skema_magang' => $this->skema_magang,
            'topik_magang' => $this->topik_magang,
            'output_target' => $this->output_target,
            'company_name' => $this->company_name,
            'alamat_perusahaan' => $this->alamat_perusahaan,
            'nama_pimpinan' => $this->nama_pimpinan,
            'tanggal_mulai' => $this->tanggal_mulai?->toDateString(),
            'tanggal_selesai' => $this->tanggal_selesai?->toDateString(),
            'final_score' => $this->final_score,
            'letter_grade' => $this->letter_grade,
            'loa_path' => $this->loa_path,
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
