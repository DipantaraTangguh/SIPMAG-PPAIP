<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function indexForPpaip()
    {
        $students = Student::with(['dpm:id,lecturer_name'])
            ->select([
                'id', 'nim', 'name', 'study_program', 'email',
                'access_status', 'is_independent', 'dpm_id',
                'approved_logbook_count', 'updated_at',
            ])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(['students' => $students]);
    }
    public function indexForKaprodi(Request $request)
    {
        $lecturer = $request->user()->lecturer;
        if (!$lecturer || !$lecturer->study_program) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $students = Student::where('study_program', $lecturer->study_program)
            ->with(['dpm:id,lecturer_name'])
            ->select([
                'id', 'nim', 'name', 'study_program', 'email',
                'access_status', 'is_independent', 'dpm_id',
                'approved_logbook_count', 'updated_at',
            ])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(['students' => $students]);
    }
    public function indexForDpm(Request $request)
    {
        $lecturer = $request->user()->lecturer;
        if (!$lecturer) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $students = Student::where('dpm_id', $lecturer->id)
            ->select([
                'id', 'nim', 'name', 'study_program', 'email',
                'access_status', 'approved_logbook_count', 'dpm_id', 'updated_at',
            ])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(['students' => $students]);
    }
}
