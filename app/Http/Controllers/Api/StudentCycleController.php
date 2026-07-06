<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InternshipCycleResource;
use App\Services\InternshipCycleResetService;
use Illuminate\Http\Request;
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
