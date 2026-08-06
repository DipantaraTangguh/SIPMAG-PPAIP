<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InternshipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'position' => $this->position,
            'description' => $this->description,
            'capacity' => $this->capacity,
            'duration' => $this->duration,
            'study_programs' => $this->study_programs ?? [],
            'start_date' => $this->start_date?->toDateString(),
            'job_description' => $this->job_description,
            'skills' => $this->skills,
            'requirements' => $this->requirements,
            'minimum_education' => $this->minimum_education,
            'sistem_kerja' => $this->sistem_kerja,
            'location' => $this->location,
            'deadline' => $this->deadline?->toDateString(),
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
