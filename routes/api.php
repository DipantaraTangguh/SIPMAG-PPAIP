<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Form1Controller;
use App\Http\Controllers\Api\InternshipController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\Form2Controller;
use App\Http\Controllers\Api\SupervisorController;
use App\Http\Controllers\Api\LogbookController;
use App\Http\Controllers\Api\SidangController;

/*
|--------------------------------------------------------------------------
| SIPMAG API Routes
|--------------------------------------------------------------------------
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

        // Sidang
        Route::get('/sidang', [SidangController::class, 'show']);
        Route::post('/sidang', [SidangController::class, 'store']);
    });

    // ── Kaprodi routes ──────────────────────────────
    Route::middleware('role:kaprodi')->prefix('kaprodi')->group(function () {
        Route::get('/form1', [Form1Controller::class, 'indexForKaprodi']);
        Route::post('/form1/{studentId}/approve', [Form1Controller::class, 'approve']);
        Route::post('/form1/{studentId}/reject', [Form1Controller::class, 'reject']);

        Route::get('/supervisor-applications', [SupervisorController::class, 'indexForKaprodi']);
        Route::post('/assign-dpm', [SupervisorController::class, 'assignDpm']);
    });

    // ── DPM routes ──────────────────────────────────
    Route::middleware('role:dpm')->prefix('dpm')->group(function () {
        Route::get('/logbooks', [LogbookController::class, 'indexForDpm']);
        Route::post('/logbooks/{id}/approve', [LogbookController::class, 'approve']);
        Route::post('/logbooks/{id}/reject', [LogbookController::class, 'reject']);
    });

    // ── PPAIP routes ────────────────────────────────
    Route::middleware('role:ppaip')->prefix('ppaip')->group(function () {
        // Vacancy CRUD
        Route::post('/internships', [InternshipController::class, 'store']);
        Route::put('/internships/{id}', [InternshipController::class, 'update']);
        Route::delete('/internships/{id}', [InternshipController::class, 'destroy']);

        // Form 2 review
        Route::get('/form2', [Form2Controller::class, 'indexForPpaip']);
        Route::post('/form2/{id}/approve', [Form2Controller::class, 'approve']);
        Route::post('/form2/{id}/reject', [Form2Controller::class, 'reject']);

        // Sidang management
        Route::get('/sidang', [SidangController::class, 'indexForPpaip']);
        Route::post('/sidang/{studentId}/complete', [SidangController::class, 'completeCycle']);
    });
});
