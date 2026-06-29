<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Form1Resource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $approver = $this->form1Approver;
        $form1 = $this->form1_data;

        // Data cycle lama sempat dihapus saat completion. Tetap kirim ringkasan
        // akademik agar halaman Profil tidak terjebak di loading state.
        if ($form1 === null && $this->access_status === 'SiklusSelesai') {
            $form1 = [
                'semester' => $this->semester,
                'jumlahSKS' => $this->jumlah_sks,
                'ipk' => $this->ipk,
                'skemaMagang' => null,
                'topikMagang' => null,
                'outputTarget' => null,
                'isArchivedSummary' => true,
            ];
        }

        return [
            'form1' => $form1,
            'access_status' => $this->access_status,
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
