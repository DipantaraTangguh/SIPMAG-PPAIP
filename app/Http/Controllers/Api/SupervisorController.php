<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\SupervisorApplication;
use Illuminate\Http\Request;

class SupervisorController extends Controller
{
    /**
     * GET /api/supervisor-application
     * Get current student's supervisor application.
     */
    public function show(Request $request)
    {
        $student = $request->user()->student;

        $application = SupervisorApplication::where('student_id', $student->id)->first();

        return response()->json([
            'application' => $application,
            'dpm'         => $student->dpm ? [
                'name'    => $student->dpm->lecturer_name,
                'nidn'    => $student->dpm->nidn,
                'contact' => $student->dpm->contact,
            ] : null,
        ]);
    }

    /**
     * POST /api/supervisor-application
     * Submit supervisor application form with LoA.
     */
    public function store(Request $request)
    {
        $student = $request->user()->student;

        // Guard: student must have completed prior steps
        // Mitra: Form1 Approved → Applied to vacancy (HasApplication)
        // Mandiri: Form1 Approved → Form2 submitted & approved (HasApplication)
        if (!in_array($student->access_status, ['HasApplication'])) {
            return response()->json(['message' => 'Anda belum menyelesaikan tahap sebelumnya.'], 403);
        }

        if (SupervisorApplication::where('student_id', $student->id)->exists()) {
            return response()->json(['message' => 'Pengajuan sudah pernah diajukan.'], 422);
        }

        $request->validate([
            'company_name'    => 'required|string|max:255',
            'company_contact' => 'required|string|max:255',
            'loa_file'        => 'required|file|mimes:pdf|max:2048',
        ]);

        $loaPath = $request->file('loa_file')->store('loa', 'local');

        SupervisorApplication::create([
            'student_id'      => $student->id,
            'company_name'    => $request->company_name,
            'company_contact' => $request->company_contact,
            'loa_path'        => $loaPath,
        ]);

        return response()->json(['message' => 'Pengajuan pembimbing berhasil dikirim.'], 201);
    }

    /**
     * GET /api/kaprodi/supervisor-applications
     * List supervisor applications for Kaprodi (scoped by study_program).
     */
    public function indexForKaprodi(Request $request)
    {
        $lecturer = $request->user()->lecturer;

        $applications = SupervisorApplication::whereHas('student', function ($q) use ($lecturer) {
            $q->where('study_program', $lecturer->study_program);
        })
        ->with('student:id,nim,name,study_program,dpm_id')
        ->orderByDesc('submitted_at')
        ->get();

        return response()->json(['applications' => $applications]);
    }

    /**
     * POST /api/kaprodi/assign-dpm
     * Assign a DPM lecturer to a student.
     */
    public function assignDpm(Request $request)
    {
        $request->validate([
            'student_id'  => 'required|exists:students,id',
            'lecturer_id' => 'required|exists:lecturers,id',
        ]);

        $lecturer = $request->user()->lecturer;
        $student = Student::where('id', $request->student_id)
            ->where('study_program', $lecturer->study_program)
            ->firstOrFail();

        // Guard: student must have submitted a supervisor nomination
        if (!$student->supervisorApplication()->exists()) {
            return response()->json([
                'message' => 'Mahasiswa belum mengajukan pengajuan pembimbing.',
            ], 422);
        }

        // Guard: student must not already have a DPM
        if ($student->dpm_id) {
            return response()->json([
                'message' => 'DPM sudah ditugaskan untuk mahasiswa ini.',
            ], 422);
        }

        $student->update([
            'dpm_id'        => $request->lecturer_id,
            'access_status' => 'HasDPM',
        ]);

        return response()->json([
            'message' => 'DPM berhasil ditugaskan.',
            'access_status' => 'HasDPM',
        ]);
    }
}
