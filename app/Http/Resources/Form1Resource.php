<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Form1Resource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $approver = $this->form1Approver;

        return [
            'form1' => $this->form1_data,
            'access_status' => $this->access_status,
            'has_completed_wajib' => $this->has_completed_wajib,
            'pdf_path' => $this->form1_pdf_path,
            'rejection_reason' => $this->form1_rejection_reason,
            'approver' => $approver ? [
                'name' => $approver->lecturer_name,
                'nidn' => $approver->nidn,
                'role' => 'Kaprodi '.$approver->study_program,
                'approvalDate' => $this->form1_approved_at?->format('d/m/Y'),
            ] : null,
            'submitted_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
