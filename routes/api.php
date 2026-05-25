<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Form1Controller;
use App\Http\Controllers\Api\InternshipController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\Form2Controller;
use App\Http\Controllers\Api\SupervisorController;
use App\Http\Controllers\Api\LogbookController;
use App\Http\Controllers\Api\DefenseController;
use App\Http\Controllers\Api\StudentController;

/*
|--------------------------------------------------------------------------
| Portal Magang API Routes
|--------------------------------------------------------------------------
|
| Role responsibilities:
|   PPAIP   → Form 2 approve/reject only. Read-only all students. Internship CRUD.
|   Kaprodi → Form 1, supervisor apps, assign DPM, sidang. Scoped to own prodi.
|   DPM     → Logbook approve/reject. Scoped to assigned students only.
|
*/

// ── Public ──────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ── All authenticated users ─────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Internships — browsable by all authenticated users
    Route::get('/internships', [InternshipController::class, 'index']);
    Route::get('/internships/{id}', [InternshipController::class, 'show']);

    // ── Mahasiswa routes ────────────────────────────
    Route::middleware('role:mahasiswa')->group(function () {
        // Form 1
        Route::get('/form1', [Form1Controller::class, 'show']);
        Route::post('/form1', [Form1Controller::class, 'store']);
        Route::get('/form1/surat-keterangan', [Form1Controller::class, 'downloadSuratKeterangan']);

        // Applications (Portal Mitra)
        Route::get('/applications', [ApplicationController::class, 'index']);
        Route::post('/applications', [ApplicationController::class, 'store']);

        // Form 2 (Mandiri)
        Route::get('/form2', [Form2Controller::class, 'index']);
        Route::post('/form2', [Form2Controller::class, 'store']);

        // Supervisor / DPM application
        Route::get('/supervisor-application', [SupervisorController::class, 'show']);
        Route::post('/supervisor-application', [SupervisorController::class, 'store']);

        // Logbook
        Route::get('/logbooks', [LogbookController::class, 'index']);
        Route::post('/logbooks', [LogbookController::class, 'store']);
        Route::put('/logbooks/{id}', [LogbookController::class, 'update']);

        // Defense
        Route::get('/defense', [DefenseController::class, 'show']);
        Route::post('/defense', [DefenseController::class, 'store']);
    });

    // ── Kaprodi routes (scoped to own prodi) ────────
    Route::middleware('role:kaprodi')->prefix('kaprodi')->group(function () {
        // Form 1 review
        Route::get('/form1', [Form1Controller::class, 'indexForKaprodi']);
        Route::post('/form1/{studentId}/approve', [Form1Controller::class, 'approve']);
        Route::post('/form1/{studentId}/reject', [Form1Controller::class, 'reject']);

        // Supervisor application review + DPM assignment
        Route::get('/supervisor-applications', [SupervisorController::class, 'indexForKaprodi']);
        Route::post('/assign-dpm', [SupervisorController::class, 'assignDpm']);

        // Sidang management (schedule + cycle completion)
        Route::get('/defense', [DefenseController::class, 'indexForKaprodi']);
        Route::post('/defense/{studentId}/schedule', [DefenseController::class, 'setSchedule']);
        Route::post('/defense/{studentId}/complete', [DefenseController::class, 'completeCycle']);

        // Students list (scoped to prodi, for Kaprodi dashboard)
        Route::get('/students', [StudentController::class, 'indexForKaprodi']);

        // Download student transcript (scoped to prodi)
        Route::get('/students/{studentId}/transkrip', [Form1Controller::class, 'downloadTranskrip']);
    });

    // ── DPM routes (scoped to assigned students) ────
    Route::middleware('role:dpm')->prefix('dpm')->group(function () {
        Route::get('/logbooks', [LogbookController::class, 'indexForDpm']);
        Route::post('/logbooks/{id}/approve', [LogbookController::class, 'approve']);
        Route::post('/logbooks/{id}/reject', [LogbookController::class, 'reject']);

        // Students assigned to this DPM
        Route::get('/students', [StudentController::class, 'indexForDpm']);
    });

    // ── PPAIP routes (cross-program) ────────────────
    Route::middleware('role:ppaip')->prefix('ppaip')->group(function () {
        // Form 2 review (only PPAIP responsibility)
        Route::get('/form2', [Form2Controller::class, 'indexForPpaip']);
        Route::post('/form2/{id}/approve', [Form2Controller::class, 'approve']);
        Route::post('/form2/{id}/reject', [Form2Controller::class, 'reject']);

        // Read-only student overview (all programs)
        Route::get('/students', [StudentController::class, 'indexForPpaip']);

        // Internship CRUD (cross-program vacancy management)
        Route::post('/internships', [InternshipController::class, 'store']);
        Route::put('/internships/{id}', [InternshipController::class, 'update']);
        Route::delete('/internships/{id}', [InternshipController::class, 'destroy']);
    });
});
