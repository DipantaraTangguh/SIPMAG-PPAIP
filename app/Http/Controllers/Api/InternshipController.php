<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Internships\StoreInternshipRequest;
use App\Http\Requests\Internships\UpdateInternshipRequest;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InternshipController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Internship::class);

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

        $internships = $query->orderByDesc('created_at')->paginate($this->perPage($request));

        return response()->json(['internships' => $internships]);
    }

    public function show(int $id)
    {
        $internship = Internship::query()
            ->openForApplications()
            ->findOrFail($id);

        return response()->json(['internship' => $internship]);
    }

    public function store(StoreInternshipRequest $request)
    {
        Gate::authorize('create', Internship::class);

        $validated = $request->validated();

        $internship = Internship::create($validated);

        return response()->json(['internship' => $internship], 201);
    }

    public function update(UpdateInternshipRequest $request, int $id)
    {
        $internship = Internship::findOrFail($id);
        Gate::authorize('update', $internship);

        $validated = $request->validated();

        $internship->update($validated);

        return response()->json(['internship' => $internship]);
    }

    public function destroy(int $id)
    {
        $internship = Internship::findOrFail($id);
        Gate::authorize('delete', $internship);

        $internship->delete();

        return response()->json(['message' => 'Lowongan berhasil dihapus.']);
    }
}
