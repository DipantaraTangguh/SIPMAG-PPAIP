<?php

use App\Exports\MitraApplicantsExport;
use App\Models\Application as InternshipApplication;
use App\Models\DefenseSubmission;
use App\Models\Student;
use App\Models\User;
use App\Support\DefenseDocument;
use App\Support\StoredFilePath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;



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
        if (! $application->cv_file_path) {
            abort(404, 'CV tidak tersedia.');
        }

        $path = StoredFilePath::resolve(storage_path('app/private'), $application->cv_file_path);
        if (! $path) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->file($path, [
            'Content-Type' => mime_content_type($path),
        ]);
    })->name('mitra-applications.cv.preview');

    Route::get('/{application}/cv/download', function (Request $request, InternshipApplication $application) {
        abort_unless($request->user()?->isPpaip(), 403);
        Gate::authorize('view', $application);
        if (! $application->cv_file_path) {
            abort(404, 'CV tidak tersedia.');
        }

        $path = StoredFilePath::resolve(storage_path('app/private'), $application->cv_file_path);
        if (! $path) {
            abort(404, 'File tidak ditemukan.');
        }

        $student = $application->student;
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        return response()->download($path, 'cv_'.($student?->nim ?? $application->id).'.'.$ext);
    })->name('mitra-applications.cv.download');
});

Route::middleware(['web', 'auth'])->prefix('admin/defense-documents')->group(function () {
    Route::get('/{submission}/{document}/preview', function (DefenseSubmission $submission, string $document) {
        Gate::authorize('view', $submission);

        $path = DefenseDocument::resolvedPath($submission, $document);
        if (! $path) {
            abort(404, 'Dokumen sidang tidak ditemukan.');
        }

        return response()->file($path, [
            'Content-Type' => mime_content_type($path),
        ]);
    })->whereIn('document', DefenseDocument::keys())->name('defense-documents.preview');

    Route::get('/{submission}/{document}/download', function (DefenseSubmission $submission, string $document) {
        Gate::authorize('view', $submission);

        $path = DefenseDocument::resolvedPath($submission, $document);
        if (! $path) {
            abort(404, 'Dokumen sidang tidak ditemukan.');
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $student = $submission->student;
        $label = str($document)->replace('_', '-');

        return response()->download($path, "sidang_{$student?->nim}_{$label}.{$ext}");
    })->whereIn('document', DefenseDocument::keys())->name('defense-documents.download');
});

// Sisanya lempar ke React router.
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api|admin|filament|_debugbar).*$');
