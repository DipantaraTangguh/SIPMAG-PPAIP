<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DefenseSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'laporan_path' => $this->laporan_path,
            'poster_path' => $this->poster_path,
            'foto_kegiatan_1_path' => $this->foto_kegiatan_1_path,
            'foto_kegiatan_2_path' => $this->foto_kegiatan_2_path,
            'krs_path' => $this->krs_path,
            'status' => $this->status,
            'scheduled_date' => $this->scheduled_date?->toDateString(),
            'scheduled_time' => $this->scheduled_time,
            'room' => $this->room,
            'dosen_penguji_1' => $this->dosen_penguji_1,
            'dosen_penguji_2' => $this->dosen_penguji_2,
            'submitted_at' => $this->submitted_at?->toJSON(),
            'scheduled_at' => $this->scheduled_at?->toJSON(),
            'student' => StudentResource::make($this->whenLoaded('student')),
            'scheduler' => LecturerResource::make($this->whenLoaded('scheduler')),
        ];
    }
}
