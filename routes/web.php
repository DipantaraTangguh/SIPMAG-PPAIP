<?php

use App\Exports\MitraApplicantsExport;
use App\Models\Application as InternshipApplication;
use App\Models\DefenseSubmission;
use App\Models\Student;
use App\Models\SupervisorApplication;
use App\Support\DefenseDocument;
use App\Support\StoredFilePath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

// Kirim file privat setelah divalidasi StoredFilePath: preview inline,
// atau download dengan nama file bila $downloadName diisi (tanpa ekstensi).
if (! function_exists('serveStoredFile')) {
    function serveStoredFile(?string $storedPath, string $notFoundMessage, ?string $downloadName = null)
    {
        $path = StoredFilePath::resolve(storage_path('app/private'), $storedPath);
        if (! $path) {
            abort(404, $notFoundMessage);
        }

        if ($downloadName !== null) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);

            return response()->download($path, "{$downloadName}.{$ext}");
        }

        return response()->file($path, [
            'Content-Type' => mime_content_type($path),
        ]);
    }
}

Route::middleware(['web', 'auth'])->prefix('admin/mitra-applications')->group(function () {
    Route::get('/export', function (Request $request) {
        abort_unless($request->user()?->isPpaip(), 403);
        Gate::authorize('viewAny', InternshipApplication::class);

        $filename = 'pelamar_mitra_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new MitraApplicantsExport, $filename, ExcelWriter::XLSX);
    })->name('mitra-applications.export');

    Route::get('/{application}/cv/preview', function (Request $request, InternshipApplication $application) {
        abort_unless($request->user()?->isPpaip(), 403);
        Gate::authorize('view', $application);

        return serveStoredFile($application->cv_file_path, 'CV tidak tersedia.');
    })->name('mitra-applications.cv.preview');

    Route::get('/{application}/cv/download', function (Request $request, InternshipApplication $application) {
        abort_unless($request->user()?->isPpaip(), 403);
        Gate::authorize('view', $application);

        $name = 'cv_'.($application->student?->nim ?? $application->id);

        return serveStoredFile($application->cv_file_path, 'CV tidak tersedia.', $name);
    })->name('mitra-applications.cv.download');
});

Route::middleware(['web', 'auth'])->prefix('admin/defense-documents')->group(function () {
    Route::get('/{submission}/{document}/preview', function (DefenseSubmission $submission, string $document) {
        Gate::authorize('view', $submission);

        return serveStoredFile(DefenseDocument::storedPath($submission, $document), 'Dokumen sidang tidak ditemukan.');
    })->whereIn('document', DefenseDocument::keys())->name('defense-documents.preview');

    Route::get('/{submission}/{document}/download', function (DefenseSubmission $submission, string $document) {
        Gate::authorize('view', $submission);

        $name = 'sidang_'.$submission->student?->nim.'_'.str($document)->replace('_', '-');

        return serveStoredFile(DefenseDocument::storedPath($submission, $document), 'Dokumen sidang tidak ditemukan.', $name);
    })->whereIn('document', DefenseDocument::keys())->name('defense-documents.download');
});

Route::middleware(['web', 'auth'])->prefix('admin/dpm/loa')->group(function () {
    Route::get('/{student}/preview', function (Request $request, Student $student) {
        $user = $request->user();
        abort_unless($user?->role === 'dpm' && $user->lecturer?->id === $student->dpm_id, 403);

        return serveStoredFile($student->supervisorApplication?->loa_path, 'LoA tidak tersedia.');
    })->name('dpm.loa.preview');

    Route::get('/{student}/download', function (Request $request, Student $student) {
        $user = $request->user();
        abort_unless($user?->role === 'dpm' && $user->lecturer?->id === $student->dpm_id, 403);

        return serveStoredFile($student->supervisorApplication?->loa_path, 'LoA tidak tersedia.', "loa_{$student->nim}");
    })->name('dpm.loa.download');
});

// Kaprodi perlu memverifikasi keaslian LoA sebelum menunjuk DPM.
Route::middleware(['web', 'auth'])->prefix('admin/kaprodi/loa')->group(function () {
    Route::get('/{student}/preview', function (Student $student) {
        /** @var SupervisorApplication $application */
        $application = $student->supervisorApplication()->firstOrFail();
        Gate::authorize('view', $application);

        return serveStoredFile($application->loa_path, 'LoA tidak tersedia.');
    })->name('kaprodi.loa.preview');

    Route::get('/{student}/download', function (Student $student) {
        /** @var SupervisorApplication $application */
        $application = $student->supervisorApplication()->firstOrFail();
        Gate::authorize('view', $application);

        return serveStoredFile($application->loa_path, 'LoA tidak tersedia.', "loa_{$student->nim}");
    })->name('kaprodi.loa.download');
});

// Sisanya lempar ke React router.
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api|admin|filament|_debugbar).*$');
