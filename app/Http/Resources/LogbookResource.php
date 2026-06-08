<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogbookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'tanggal' => $this->tanggal?->toDateString(),
            'kegiatan_harian' => $this->kegiatan_harian,
            'hasil' => $this->hasil,
            'status' => $this->status,
            'dpm_note' => $this->dpm_note,
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
            'student' => StudentResource::make($this->whenLoaded('student')),
        ];
    }
}
