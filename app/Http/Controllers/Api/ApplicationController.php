<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Internship;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    /**
     * GET /api/applications
     * List current student's applications with internship data.
     */
    public function index(Request $request)
    {
        $student = $request->user()->student;

        $applications = Application::where('student_id', $student->id)
            ->with('internship:id,company_name,position,location,deadline')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['applications' => $applications]);
    }

    /**
     * POST /api/applications
     * Apply to an internship (upload CV). Max 5 simultaneous Applied.
     */
    public function store(Request $request)
    {
        $student = $request->user()->student;

        if (!in_array($student->access_status, ['ApprovedForm1', 'HasApplication'])) {
            return response()->json(['message' => 'Form 1 harus disetujui terlebih dahulu.'], 403);
        }

        $request->validate([
            'internship_id' => 'required|exists:internships,id',
            'cv_file'       => 'required|file|mimes:pdf|max:5120',
        ]);

        // Check max 5 concurrent Applied applications
        $activeCount = Application::where('student_id', $student->id)
            ->where('status', 'Applied')
            ->count();

        if ($activeCount >= 5) {
            return response()->json(['message' => 'Maksimal 5 lamaran aktif.'], 422);
        }

        // Check duplicate application to same internship
        $exists = Application::where('student_id', $student->id)
            ->where('internship_id', $request->internship_id)
            ->whereIn('status', ['Applied', 'Accepted'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Anda sudah melamar lowongan ini.'], 422);
        }

        $cvPath = $request->file('cv_file')->store('cv', 'local');

        $application = Application::create([
            'student_id'    => $student->id,
            'internship_id' => $request->internship_id,
            'cv_file_path'  => $cvPath,
            'status'        => 'Applied',
        ]);

        // Update access status if first application
        if ($student->access_status === 'ApprovedForm1') {
            $student->update(['access_status' => 'HasApplication']);
        }

        return response()->json([
            'message'     => 'Lamaran berhasil dikirim.',
            'application' => $application->load('internship:id,company_name,position'),
        ], 201);
    }
}
