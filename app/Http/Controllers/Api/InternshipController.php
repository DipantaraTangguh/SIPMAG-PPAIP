<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use Illuminate\Http\Request;

class InternshipController extends Controller
{
    /**
     * GET /api/internships
     * List active internship vacancies. Supports search by company/position.
     */
    public function index(Request $request)
    {
        $query = Internship::where('is_active', true);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($location = $request->query('location')) {
            $query->where('location', $location);
        }

        $internships = $query->orderByDesc('created_at')->get();

        return response()->json(['internships' => $internships]);
    }

    /**
     * GET /api/internships/{id}
     * Get single internship detail.
     */
    public function show($id)
    {
        $internship = Internship::findOrFail($id);

        return response()->json(['internship' => $internship]);
    }

    /**
     * POST /api/ppaip/internships
     * Create new vacancy (PPAIP only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name'      => 'required|string|max:255',
            'position'          => 'required|string|max:255',
            'description'       => 'required|string',
            'vacancy_details'   => 'nullable|string',
            'job_description'   => 'nullable|array',
            'minimum_education' => 'nullable|string',
            'sistem_kerja'      => 'nullable|string',
            'location'          => 'nullable|string',
            'deadline'          => 'required|date',
            'is_active'         => 'boolean',
        ]);

        $internship = Internship::create($validated);

        return response()->json(['internship' => $internship], 201);
    }

    /**
     * PUT /api/ppaip/internships/{id}
     */
    public function update(Request $request, $id)
    {
        $internship = Internship::findOrFail($id);

        $validated = $request->validate([
            'company_name'      => 'sometimes|string|max:255',
            'position'          => 'sometimes|string|max:255',
            'description'       => 'sometimes|string',
            'vacancy_details'   => 'nullable|string',
            'job_description'   => 'nullable|array',
            'minimum_education' => 'nullable|string',
            'sistem_kerja'      => 'nullable|string',
            'location'          => 'nullable|string',
            'deadline'          => 'sometimes|date',
            'is_active'         => 'boolean',
        ]);

        $internship->update($validated);

        return response()->json(['internship' => $internship]);
    }

    /**
     * DELETE /api/ppaip/internships/{id}
     */
    public function destroy($id)
    {
        Internship::findOrFail($id)->delete();

        return response()->json(['message' => 'Lowongan berhasil dihapus.']);
    }
}
