<?php

use Illuminate\Support\Facades\Route;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Transcript file routes (Filament / session auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])->prefix('admin/transkrip')->group(function () {

    // Inline preview (opens in iframe)
    Route::get('/{student}/preview', function (Student $student) {
        $user = Auth::user();
        if (!in_array($user->role, ['kaprodi', 'ppaip'])) {
            abort(403);
        }
        if ($user->role === 'kaprodi') {
            $prodi = $user->lecturer?->study_program;
            if ($student->study_program !== $prodi) {
                abort(403);
            }
        }
        if (!$student->form1_pdf_path) {
            abort(404, 'Transkrip tidak tersedia.');
        }
        $path = storage_path('app/private/' . $student->form1_pdf_path);
        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }
        return response()->file($path, [
            'Content-Type' => mime_content_type($path),
        ]);
    })->name('transkrip.preview');

    // Force download
    Route::get('/{student}/download', function (Student $student) {
        $user = Auth::user();
        if (!in_array($user->role, ['kaprodi', 'ppaip'])) {
            abort(403);
        }
        if ($user->role === 'kaprodi') {
            $prodi = $user->lecturer?->study_program;
            if ($student->study_program !== $prodi) {
                abort(403);
            }
        }
        if (!$student->form1_pdf_path) {
            abort(404);
        }
        $path = storage_path('app/private/' . $student->form1_pdf_path);
        if (!file_exists($path)) {
            abort(404);
        }
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        return response()->download($path, "transkrip_{$student->nim}.{$ext}");
    })->name('transkrip.download');

    // Bulk download as ZIP
    Route::post('/bulk-download', function (Request $request) {
        $user = Auth::user();
        if (!in_array($user->role, ['kaprodi', 'ppaip'])) {
            abort(403);
        }

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

        $zipName = 'transkrip_' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path('app/private/temp/' . $zipName);

        // Ensure temp directory exists
        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($students as $student) {
            $filePath = storage_path('app/private/' . $student->form1_pdf_path);
            if (file_exists($filePath)) {
                $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                $zip->addFile($filePath, "transkrip_{$student->nim}_{$student->name}.{$ext}");
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    })->name('transkrip.bulk-download');
});

// React SPA Catch-all Route
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api|admin|filament|_debugbar).*$');
