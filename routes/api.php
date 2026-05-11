<?php 

use App\Http\Controllers\Api\AuthController; // Import AuthController
use App\Http\Controllers\Api\PerizinanController;
use App\Http\Controllers\Api\SantriController;
use App\Http\Controllers\Api\TemplateVariableController;
use App\Http\Controllers\Api\AbsensiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Tanpa Login)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| Protected Routes (Wajib Login/Token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Grouping route santri
    Route::prefix('santri')->group(function () {
        Route::get('/', [SantriController::class, 'index']);
        Route::get('/filters', [SantriController::class, 'getFilterData']);
        Route::get('/{id}', [SantriController::class, 'show']);
    });

    // Grouping route mobile (Perizinan)
    Route::prefix('mobile')->group(function () {
        Route::get('/templates', [PerizinanController::class, 'templates']);
        Route::get('/template-variables', [TemplateVariableController::class, 'index']);

        Route::get('/perizinan', [PerizinanController::class, 'index']);
        Route::get('/perizinan/{id}', [PerizinanController::class, 'show']);

        Route::post('/perizinan', [PerizinanController::class, 'store']);

        Route::post('/perizinan/scan', [PerizinanController::class, 'scan']);
        Route::post('/perizinan/{id}/manual', [PerizinanController::class, 'manual']);

        Route::get('/absensi', [AbsensiController::class, 'index']);
        Route::post('/absensi', [AbsensiController::class, 'store']);

    });



});