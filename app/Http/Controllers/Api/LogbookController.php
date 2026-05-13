<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use Illuminate\Http\Request;

class LogbookController extends Controller
{
    /**
     * GET /api/logbooks
     * List current student's logbook entries.
     */
    public function index(Request $request)
    {
        $student = $request->user()->student;

        $logbooks = Logbook::where('student_id', $student->id)
            ->orderByDesc('tanggal')
            ->get();

        return response()->json([
            'logbooks'               => $logbooks,
            'approved_logbook_count' => $student->approved_logbook_count,
        ]);
    }

    /**
     * POST /api/logbooks
     * Submit a new logbook entry.
     */
    public function store(Request $request)
    {
        $student = $request->user()->student;

        if ($student->access_status !== 'HasDPM' && $student->access_status !== 'LogbookComplete') {
            return response()->json(['message' => 'DPM harus ditugaskan terlebih dahulu.'], 403);
        }

        $validated = $request->validate([
            'tanggal'          => 'required|date',
            'kegiatan_harian'  => 'required|string',
            'hasil'            => 'required|string',
        ]);

        $validated['student_id'] = $student->id;

        $logbook = Logbook::create($validated);

        return response()->json(['message' => 'Logbook berhasil disimpan.', 'logbook' => $logbook], 201);
    }

    /**
     * PUT /api/logbooks/{id}
     * Update a logbook entry (only if PendingReview or Rejected).
     */
    public function update(Request $request, $id)
    {
        $student = $request->user()->student;

        $logbook = Logbook::where('id', $id)
            ->where('student_id', $student->id)
            ->whereIn('status', ['PendingReview', 'Rejected'])
            ->firstOrFail();

        $validated = $request->validate([
            'tanggal'          => 'sometimes|date',
            'kegiatan_harian'  => 'sometimes|string',
            'hasil'            => 'sometimes|string',
        ]);

        // Reset to PendingReview on edit
        $validated['status']   = 'PendingReview';
        $validated['dpm_note'] = null;

        $logbook->update($validated);

        return response()->json(['message' => 'Logbook berhasil diperbarui.', 'logbook' => $logbook]);
    }

    /**
     * GET /api/dpm/logbooks
     * List logbook entries for assigned students (DPM only).
     */
    public function indexForDpm(Request $request)
    {
        $lecturer = $request->user()->lecturer;

        $logbooks = Logbook::whereHas('student', function ($q) use ($lecturer) {
            $q->where('dpm_id', $lecturer->id);
        })
        ->with('student:id,nim,name')
        ->orderByDesc('tanggal')
        ->get();

        return response()->json(['logbooks' => $logbooks]);
    }

    /**
     * POST /api/dpm/logbooks/{id}/approve
     * DPM can only approve logbooks of students assigned to them.
     */
    public function approve(Request $request, $id)
    {
        $lecturer = $request->user()->lecturer;

        $logbook = Logbook::where('id', $id)
            ->where('status', 'PendingReview')
            ->whereHas('student', fn ($q) => $q->where('dpm_id', $lecturer->id))
            ->firstOrFail();

        $logbook->update(['status' => 'Approved', 'dpm_note' => null]);

        // Increment student's approved count
        $student = $logbook->student;
        $student->increment('approved_logbook_count');

        // Auto-upgrade status if 6 approved
        if ($student->approved_logbook_count >= 6 && $student->access_status === 'HasDPM') {
            $student->update(['access_status' => 'LogbookComplete']);
        }

        return response()->json([
            'message'                => 'Logbook disetujui.',
            'approved_logbook_count' => $student->fresh()->approved_logbook_count,
        ]);
    }

    /**
     * POST /api/dpm/logbooks/{id}/reject
     * DPM can only reject logbooks of students assigned to them.
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        $lecturer = $request->user()->lecturer;

        $logbook = Logbook::where('id', $id)
            ->where('status', 'PendingReview')
            ->whereHas('student', fn ($q) => $q->where('dpm_id', $lecturer->id))
            ->firstOrFail();

        $logbook->update([
            'status'   => 'Rejected',
            'dpm_note' => $request->note,
        ]);

        return response()->json(['message' => 'Logbook ditolak.']);
    }
}
