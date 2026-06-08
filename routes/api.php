<?php 

use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KategoriPelanggaranController;
use App\Http\Controllers\Api\PelanggaranSantriController;
use App\Http\Controllers\Api\PerizinanController;
use App\Http\Controllers\Api\SantriController;
use App\Http\Controllers\Api\TemplateVariableController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Santri Data
    Route::prefix('santri')->middleware('permission:manage_santri')->group(function () {
        Route::get('/', [SantriController::class, 'index']);
        Route::get('/filters', [SantriController::class, 'getFilterData']);
        Route::get('/{id}', [SantriController::class, 'show']);
    });

    // Mobile Modules (Perizinan & Absensi)
    Route::prefix('mobile')->group(function () {
        
        // --- Perizinan Module ---
        Route::middleware('permission:manage_perizinan')->group(function () {
            Route::get('/templates', [PerizinanController::class, 'templates']);
            Route::get('/template-variables', [TemplateVariableController::class, 'index']);
            Route::get('/perizinan', [PerizinanController::class, 'index']);
            Route::get('/perizinan/{id}', [PerizinanController::class, 'show']);
            Route::post('/perizinan', [PerizinanController::class, 'store']);
            Route::post('/perizinan/scan', [PerizinanController::class, 'scan']);
            Route::post('/perizinan/{id}/manual', [PerizinanController::class, 'manual']);
        });

        // --- Absensi Module ---
        Route::prefix('absensi')->middleware('permission:manage_absensi')->group(function () {
            Route::get('/sesi', [AbsensiController::class, 'getSesi']); // Daftar sesi untuk dashboard
            Route::get('/santri/{sesi_id}', [AbsensiController::class, 'getSantriBySesi']); // List santri per sesi
            Route::post('/scan', [AbsensiController::class, 'scanSantri']); // Ambil data santri via QR
            Route::post('/simpan', [AbsensiController::class, 'store']); // Eksekusi absensi (Manual/QR)
            Route::post('/hapus', [AbsensiController::class, 'destroy']);
        });

        // --- 2. Pelanggaran Module (Kategori Pelanggaran) ---
        Route::middleware('permission:manage_pelanggaran')->group(function () {
            Route::apiResource('kategori-pelanggaran', KategoriPelanggaranController::class);
            Route::post('pelanggaran', [PelanggaranSantriController::class, 'store']);
            Route::put('pelanggaran/{id}', [PelanggaranSantriController::class, 'update']);
            Route::delete('pelanggaran/{id}', [PelanggaranSantriController::class, 'destroy']);
        });

    });

});