<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Applications\StoreApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\Internship;
use App\Models\Student;
use App\Services\StudentStateMachine;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ApplicationController extends Controller
{
    private const SECURED_INTERNSHIP_STATUSES = [
        'HasDPM',
        'LogbookComplete',
        'MenungguSidang',
        'SiklusSelesai',
        'SelesaiNonWajib',
        'MenungguKonfirmasi',
    ];

    private const SECURED_INTERNSHIP_MESSAGE = 'DPM Anda sudah ditunjuk atau pengajuan DPM sudah disetujui, sehingga Anda tidak dapat melamar lowongan mitra lagi.';

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Application::class);

        $student = $request->user()->student;

        $applications = Application::where('student_id', $student->id)
            ->with('internship:id,company_name,position,location,deadline')
            ->orderByDesc('created_at')
            ->paginate($this->perPage($request));

        return response()->json([
            'applications' => $this->resourceCollection($request, ApplicationResource::class, $applications),
        ]);
    }

    public function store(StoreApplicationRequest $request)
    {
        Gate::authorize('create', Application::class);

        $validated = $request->validated();

        $studentId = $request->user()->student?->id;
        if (! $studentId) {
            return response()->json(['message' => 'Profil mahasiswa tidak ditemukan.'], 404);
        }

        $cvPath = null;

        try {
            $application = DB::transaction(function () use ($request, $validated, $studentId, &$cvPath) {
                $student = Student::query()->lockForUpdate()->findOrFail($studentId);

                if ($this->studentHasSecuredInternship($student)) {
                    throw ValidationException::withMessages([
                        'internship_id' => self::SECURED_INTERNSHIP_MESSAGE,
                    ]);
                }

                if (! in_array($student->access_status, ['ApprovedForm1', 'HasApplication'])) {
                    abort(403, 'Form 1 harus disetujui terlebih dahulu.');
                }

                $internship = Internship::query()
                    ->openForApplications()
                    ->lockForUpdate()
                    ->find($validated['internship_id']);

                if (! $internship) {
                    throw ValidationException::withMessages([
                        'internship_id' => 'Lowongan tidak aktif atau batas waktu pendaftaran telah berakhir.',
                    ]);
                }

                $alreadyApplied = Application::where('student_id', $student->id)
                    ->where('internship_id', $internship->id)
                    ->exists();

                if ($alreadyApplied) {
                    throw ValidationException::withMessages([
                        'internship_id' => 'Anda sudah melamar lowongan ini.',
                    ]);
                }

                $cvPath = $request->file('cv_file')->store('cv', 'local');

                $application = Application::create([
                    'student_id' => $student->id,
                    'internship_id' => $internship->id,
                    'cv_file_path' => $cvPath,
                    'status' => 'Applied',
                ]);

                if ($student->access_status === 'ApprovedForm1') {
                    app(StudentStateMachine::class)->transition($student, 'HasApplication');
                }

                return $application;
            });
        } catch (UniqueConstraintViolationException $exception) {
            if ($cvPath) {
                Storage::disk('local')->delete($cvPath);
            }

            throw ValidationException::withMessages([
                'internship_id' => 'Anda sudah melamar lowongan ini.',
            ]);
        } catch (Throwable $exception) {
            if ($cvPath) {
                Storage::disk('local')->delete($cvPath);
            }

            throw $exception;
        }

        return response()->json([
            'message' => 'Lamaran berhasil dikirim.',
            'application' => ApplicationResource::make($application->load('internship:id,company_name,position'))->resolve($request),
        ], 201);
    }

    private function studentHasSecuredInternship(Student $student): bool
    {
        return in_array($student->access_status, self::SECURED_INTERNSHIP_STATUSES, true);
    }
}
