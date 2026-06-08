<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StudentController extends Controller
{
    public function indexForPpaip(Request $request)
    {
        Gate::authorize('viewAny', Student::class);

        $students = Student::with(['dpm:id,lecturer_name'])
            ->select([
                'id', 'nim', 'name', 'study_program', 'email',
                'access_status', 'is_independent', 'dpm_id',
                'updated_at',
            ])
            ->withCount(['logbooks as approved_logbook_count' => fn ($query) => $query->where('status', 'Approved')])
            ->orderByDesc('updated_at')
            ->paginate($this->perPage($request));

        return response()->json(['students' => $students]);
    }

    public function indexForKaprodi(Request $request)
    {
        Gate::authorize('viewAny', Student::class);

        $lecturer = $request->user()->lecturer;
        if (! $lecturer || ! $lecturer->study_program) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $students = Student::where('study_program', $lecturer->study_program)
            ->with(['dpm:id,lecturer_name'])
            ->select([
                'id', 'nim', 'name', 'study_program', 'email',
                'access_status', 'is_independent', 'dpm_id',
                'updated_at',
            ])
            ->withCount(['logbooks as approved_logbook_count' => fn ($query) => $query->where('status', 'Approved')])
            ->orderByDesc('updated_at')
            ->paginate($this->perPage($request));

        return response()->json(['students' => $students]);
    }

    public function indexForDpm(Request $request)
    {
        Gate::authorize('viewAny', Student::class);

        $lecturer = $request->user()->lecturer;
        if (! $lecturer) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $students = Student::where('dpm_id', $lecturer->id)
            ->select([
                'id', 'nim', 'name', 'study_program', 'email',
                'access_status', 'dpm_id', 'updated_at',
            ])
            ->withCount(['logbooks as approved_logbook_count' => fn ($query) => $query->where('status', 'Approved')])
            ->orderByDesc('updated_at')
            ->paginate($this->perPage($request));

        return response()->json(['students' => $students]);
    }
}
