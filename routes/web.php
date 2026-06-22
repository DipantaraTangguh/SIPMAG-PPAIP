<?php

use App\Models\DefenseSubmission;
use App\Models\Student;
use App\Models\User;
use App\Support\DefenseDocument;
use App\Support\StoredFilePath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin/transkrip')->group(function () {

    // Preview PDF langsung di iframe.
    Route::get('/{student}/preview', function (Request $request, Student $student) {
        Gate::authorize('viewTranscript', $student);
        if (! $student->form1_pdf_path) {
            abort(404, 'Transkrip tidak tersedia.');
        }
        $path = StoredFilePath::resolve(storage_path('app/private'), $student->form1_pdf_path);
        if (! $path) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->file($path, [
            'Content-Type' => mime_content_type($path),
        ]);
    })->name('transkrip.preview');

    // Download satu file tanpa render preview.
    Route::get('/{student}/download', function (Request $request, Student $student) {
        Gate::authorize('viewTranscript', $student);
        if (! $student->form1_pdf_path) {
            abort(404);
        }
        $path = StoredFilePath::resolve(storage_path('app/private'), $student->form1_pdf_path);
        if (! $path) {
            abort(404);
        }
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        return response()->download($path, "transkrip_{$student->nim}.{$ext}");
    })->name('transkrip.download');

    // Paketkan semua transkrip jadi ZIP.
    Route::post('/bulk-download', function (Request $request) {
        /** @var User $user */
        $user = $request->user();
        Gate::authorize('viewTranscripts', Student::class);

        $ids = $request->input('ids', []);
        $query = Student::whereIn('id', $ids)->whereNotNull('form1_pdf_path');

        if ($user->role === 'kaprodi') {
            $prodi = $user->lecturer?->study_program;
            $query->where('study_program', $prodi);
        }

        $students = $query->get();

        if ($students->isEmpty()) {
            abort(404, 'Tidak ada transkrip untuk diunduh.');
        }

        $zipName = 'transkrip_'.now()->format('Ymd_His').'.zip';
        $zipPath = storage_path('app/private/temp/'.$zipName);

        // Folder temp kadang belum ada di fresh install.
        if (! is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($students as $student) {
            $filePath = StoredFilePath::resolve(storage_path('app/private'), $student->form1_pdf_path);
            if ($filePath) {
                $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                $zip->addFile($filePath, "transkrip_{$student->nim}_{$student->name}.{$ext}");
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    })->name('transkrip.bulk-download');
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
