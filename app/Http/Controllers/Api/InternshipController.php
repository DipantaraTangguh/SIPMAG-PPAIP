<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use Illuminate\Http\Request;

class InternshipController extends Controller
{
    public function index(Request $request)
    {
        $query = Internship::query()->openForApplications();

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
    public function show(int $id)
    {
        $internship = Internship::query()
            ->openForApplications()
            ->findOrFail($id);

        return response()->json(['internship' => $internship]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name'      => 'required|string|max:255',
            'position'          => 'required|string|max:255',
            'description'       => 'required|string',
            'capacity'          => 'nullable|string|max:255',
            'duration'          => 'nullable|string|max:255',
            'bidang'            => 'nullable|string|max:255',
            'start_date'        => 'nullable|date',
            'job_description'   => 'nullable|array',
            'skills'            => 'nullable|array',
            'requirements'      => 'nullable|array',
            'minimum_education' => 'nullable|string',
            'sistem_kerja'      => 'nullable|string',
            'location'          => 'nullable|string',
            'deadline'          => 'required|date',
            'is_active'         => 'boolean',
        ]);

        $internship = Internship::create($validated);

        return response()->json(['internship' => $internship], 201);
    }
    public function update(Request $request, $id)
    {
        $internship = Internship::findOrFail($id);

        $validated = $request->validate([
            'company_name'      => 'sometimes|string|max:255',
            'position'          => 'sometimes|string|max:255',
            'description'       => 'sometimes|string',
            'capacity'          => 'nullable|string|max:255',
            'duration'          => 'nullable|string|max:255',
            'bidang'            => 'nullable|string|max:255',
            'start_date'        => 'nullable|date',
            'job_description'   => 'nullable|array',
            'skills'            => 'nullable|array',
            'requirements'      => 'nullable|array',
            'minimum_education' => 'nullable|string',
            'sistem_kerja'      => 'nullable|string',
            'location'          => 'nullable|string',
            'deadline'          => 'sometimes|date',
            'is_active'         => 'boolean',
        ]);

        $internship->update($validated);

        return response()->json(['internship' => $internship]);
    }
    public function destroy($id)
    {
        Internship::findOrFail($id)->delete();

        return response()->json(['message' => 'Lowongan berhasil dihapus.']);
    }
}
