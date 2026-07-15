<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cycle\ConfirmNonWajibRequest;
use App\Http\Resources\InternshipCycleResource;
use App\Models\Student;
use App\Services\InternshipCycleResetService;
use App\Services\InternshipCycleSnapshotService;
use App\Services\StudentStateMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StudentCycleController extends Controller
{
    public function history(Request $request)
    {
        $student = $request->user()->student;
        if (! $student) {
            return response()->json(['message' => 'Profil mahasiswa tidak ditemukan.'], 404);
        }

        Gate::authorize('view', $student);

        $cycles = $student->internshipCycles()
            ->orderByDesc('cycle_number')
            ->get();

        return response()->json([
            'cycles' => InternshipCycleResource::collection($cycles)->resolve($request),
        ]);
    }

    /**
     * Konfirmasi hasil magang non-wajib jalur Form 2 (state MenungguKonfirmasi):
     * - diterima : wajib bukti LoA + tempat & periode aktual → SelesaiNonWajib + riwayat.
     * - ditolak  : mundur ke ApprovedForm1 agar bisa mengajukan Form 2 lagi.
     */
    public function confirm(ConfirmNonWajibRequest $request, StudentStateMachine $stateMachine, InternshipCycleSnapshotService $snapshotService)
    {
        $student = $request->user()->student;
        if (! $student) {
            return response()->json(['message' => 'Profil mahasiswa tidak ditemukan.'], 404);
        }

        Gate::authorize('view', $student);

        if ($student->access_status !== 'MenungguKonfirmasi') {
            return response()->json(['message' => 'Tidak ada konfirmasi magang yang menunggu.'], 403);
        }

        $validated = $request->validated();

        if ($validated['hasil'] === 'ditolak') {
            $stateMachine->transition($student, 'ApprovedForm1');

            return response()->json([
                'message' => 'Status dicatat. Silakan ajukan Form 2 ke perusahaan lain.',
                'access_status' => 'ApprovedForm1',
            ]);
        }

        $loaPath = $request->file('loa_file')->store('loa', 'local');

        DB::transaction(function () use ($student, $stateMachine, $snapshotService, $validated, $loaPath): void {
            $locked = Student::query()->lockForUpdate()->findOrFail($student->id);

            $stateMachine->transition($locked, 'SelesaiNonWajib');
            $snapshotService->record($locked->refresh(), [
                'company_name' => $validated['company_name'],
                'alamat_perusahaan' => $validated['alamat_perusahaan'] ?? null,
                'tanggal_mulai' => $validated['tanggal_mulai'].'-01',
                'tanggal_selesai' => $validated['tanggal_selesai'].'-01',
                'loa_path' => $loaPath,
            ]);
        });

        return response()->json([
            'message' => 'Konfirmasi diterima. Magang non-wajib Anda tercatat di riwayat.',
            'access_status' => 'SelesaiNonWajib',
        ]);
    }

    public function reset(Request $request, InternshipCycleResetService $resetService)
    {
        $student = $request->user()->student;
        if (! $student) {
            return response()->json(['message' => 'Profil mahasiswa tidak ditemukan.'], 404);
        }

        Gate::authorize('resetCycle', $student);

        $resetService->reset($student);

        return response()->json([
            'message' => 'Siklus magang berhasil direset. Anda dapat mengajukan Form 1 kembali.',
            'access_status' => 'Unverified',
        ]);
    }
}
