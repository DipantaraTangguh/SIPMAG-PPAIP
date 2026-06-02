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
// Route yang boleh dihit tanpa token.
Route::post('/login', [AuthController::class, 'login']);

// Area umum buat user yang sudah login.
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Lowongan bisa dilihat semua role yang sudah login.
    Route::get('/internships', [InternshipController::class, 'index']);
    Route::get('/internships/{id}', [InternshipController::class, 'show']);

    // Flow mahasiswa, dari Form 1 sampai sidang.
    Route::middleware('role:mahasiswa')->group(function () {
        // Form 1: cek syarat akademik dulu.
        Route::get('/form1', [Form1Controller::class, 'show']);
        Route::post('/form1', [Form1Controller::class, 'store']);
        Route::get('/form1/surat-keterangan', [Form1Controller::class, 'downloadSuratKeterangan']);

        // Lamaran dari portal mitra.
        Route::get('/applications', [ApplicationController::class, 'index']);
        Route::post('/applications', [ApplicationController::class, 'store']);

        // Form 2 buat jalur mandiri.
        Route::get('/form2', [Form2Controller::class, 'index']);
        Route::post('/form2', [Form2Controller::class, 'store']);

        // Request DPM setelah jalur magang valid.
        Route::get('/supervisor-application', [SupervisorController::class, 'show']);
        Route::get('/supervisor-application/loa', [SupervisorController::class, 'downloadLoa']);
        Route::post('/supervisor-application', [SupervisorController::class, 'store']);

        // Logbook mingguan mahasiswa.
        Route::get('/logbooks', [LogbookController::class, 'index']);
        Route::post('/logbooks', [LogbookController::class, 'store']);
        Route::put('/logbooks/{id}', [LogbookController::class, 'update']);

        // Sidang baru kebuka setelah logbook beres.
        Route::get('/defense', [DefenseController::class, 'show']);
        Route::post('/defense', [DefenseController::class, 'store']);
    });

    // Kaprodi cuma main di prodi sendiri.
    Route::middleware('role:kaprodi')->prefix('kaprodi')->group(function () {
        // Review Form 1 tetap dari Kaprodi prodi terkait.
        Route::get('/form1', [Form1Controller::class, 'indexForKaprodi']);
        Route::post('/form1/{studentId}/approve', [Form1Controller::class, 'approve']);
        Route::post('/form1/{studentId}/reject', [Form1Controller::class, 'reject']);

        // Kaprodi approve request lalu pilih DPM.
        Route::get('/supervisor-applications', [SupervisorController::class, 'indexForKaprodi']);
        Route::get('/supervisor-applications/{studentId}/loa', [SupervisorController::class, 'downloadLoaForKaprodi']);
        Route::post('/assign-dpm', [SupervisorController::class, 'assignDpm']);

        // Jadwal sidang dan closing cycle magang.
        Route::get('/defense', [DefenseController::class, 'indexForKaprodi']);
        Route::post('/defense/{studentId}/schedule', [DefenseController::class, 'scheduleSidang']);
        Route::post('/defense/{studentId}/complete', [DefenseController::class, 'completeCycle']);

        // Data mahasiswa buat dashboard Kaprodi.
        Route::get('/students', [StudentController::class, 'indexForKaprodi']);

        // Transkrip tetap dibatasi per prodi.
        Route::get('/students/{studentId}/transkrip', [Form1Controller::class, 'downloadTranskrip']);
    });

    // DPM hanya pegang mahasiswa assign-an dia.
    Route::middleware('role:dpm')->prefix('dpm')->group(function () {
        Route::get('/logbooks', [LogbookController::class, 'indexForDpm']);
        Route::post('/logbooks/{id}/approve', [LogbookController::class, 'approve']);
        Route::post('/logbooks/{id}/reject', [LogbookController::class, 'reject']);

        // List anak bimbingan DPM ini.
        Route::get('/students', [StudentController::class, 'indexForDpm']);
    });

    // PPAIP lintas prodi, jadi scope-nya lebih luas.
    Route::middleware('role:ppaip')->prefix('ppaip')->group(function () {
        // Form 2 memang meja review-nya PPAIP.
        Route::get('/form2', [Form2Controller::class, 'indexForPpaip']);
        Route::post('/form2/{id}/approve', [Form2Controller::class, 'approve']);
        Route::post('/form2/{id}/reject', [Form2Controller::class, 'reject']);

        // Overview mahasiswa lintas prodi, read-only.
        Route::get('/students', [StudentController::class, 'indexForPpaip']);

        // CRUD lowongan buat semua prodi.
        Route::post('/internships', [InternshipController::class, 'store']);
        Route::put('/internships/{id}', [InternshipController::class, 'update']);
        Route::delete('/internships/{id}', [InternshipController::class, 'destroy']);
    });
});
