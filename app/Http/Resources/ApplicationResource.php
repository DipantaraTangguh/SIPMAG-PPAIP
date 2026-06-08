<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'internship_id' => $this->internship_id,
            'cv_file_path' => $this->cv_file_path,
            'loa_path' => $this->loa_path,
            'status' => $this->status,
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
            'internship' => InternshipResource::make($this->whenLoaded('internship')),
            'student' => StudentResource::make($this->whenLoaded('student')),
        ];
    }
}
