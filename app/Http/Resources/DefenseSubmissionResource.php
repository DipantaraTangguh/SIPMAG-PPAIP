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
            'status' => $this->status,
            'scheduled_date' => $this->scheduled_date?->toDateString(),
            'scheduled_time' => $this->scheduled_time,
            'room' => $this->room,
            'dosen_penguji_1_id' => $this->dosen_penguji_1_id,
            'dosen_penguji_2_id' => $this->dosen_penguji_2_id,
            'dosen_penguji_1' => $this->examinerOne?->lecturer_name,
            'dosen_penguji_2' => $this->examinerTwo?->lecturer_name,
            'submitted_at' => $this->submitted_at?->toJSON(),
            'scheduled_at' => $this->scheduled_at?->toJSON(),
            'student' => StudentResource::make($this->whenLoaded('student')),
            'examiner_one' => LecturerResource::make($this->whenLoaded('examinerOne')),
            'examiner_two' => LecturerResource::make($this->whenLoaded('examinerTwo')),
        ];
    }
}
