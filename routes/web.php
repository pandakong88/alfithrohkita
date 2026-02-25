<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Auth\LoginController;

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Protected Routes (Auth Required)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN AREA
    |--------------------------------------------------------------------------
    */

    Route::prefix('super-admin')
        ->middleware('role:super_admin')
        ->name('superadmin.')
        ->group(function () {

            // Dashboard
            Route::get('/dashboard',
                [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index']
            )->name('dashboard');

            // Pondok Management
            Route::resource('pondok',
                \App\Http\Controllers\SuperAdmin\PondokController::class
            );

            Route::patch('pondok/{pondok}/toggle',
                [\App\Http\Controllers\SuperAdmin\PondokController::class, 'toggle']
            )->name('pondok.toggle');
        });



    /*
    |--------------------------------------------------------------------------
    | TENANT AREA
    |--------------------------------------------------------------------------
    */

    Route::prefix('dashboard')
        ->name('tenant.')
        ->group(function () {

            // ================= Dashboard =================
            Route::get('/',
                [\App\Http\Controllers\Tenant\DashboardController::class, 'index']
            )->name('dashboard');


            /*
            |--------------------------------------------------------------------------
            | USER MANAGEMENT
            |--------------------------------------------------------------------------
            */
            Route::middleware('permission:manage_users')->group(function () {

                Route::resource('user',
                    \App\Http\Controllers\Tenant\UserController::class
                );

                Route::patch('user/{user}/toggle',
                    [\App\Http\Controllers\Tenant\UserController::class, 'toggle']
                )->name('user.toggle');

                Route::get('user-trash',
                    [\App\Http\Controllers\Tenant\UserController::class, 'trash']
                )->name('user.trash');

                Route::patch('user/{id}/restore',
                    [\App\Http\Controllers\Tenant\UserController::class, 'restore']
                )->name('user.restore');

                Route::resource('role',
                    \App\Http\Controllers\Tenant\RoleController::class
                );
            });


            /*
            |--------------------------------------------------------------------------
            | SANTRI MODULE
            |--------------------------------------------------------------------------
            */
            Route::middleware('permission:manage_santri')->group(function () {

                // ================= Import =================

                Route::get('santri/import',
                    [\App\Http\Controllers\Tenant\SantriController::class, 'importForm']
                )->name('santri.import.form');

                Route::post('santri/import/preview',
                    [\App\Http\Controllers\Tenant\SantriController::class, 'importPreview']
                )->name('santri.import.preview');

                Route::get('santri/import/{batch}',
                    [\App\Http\Controllers\Tenant\SantriController::class, 'importPreviewShow']
                )->whereNumber('batch')
                ->name('santri.import.preview.show');

                Route::post('santri/import/{batch}/commit',
                    [\App\Http\Controllers\Tenant\SantriController::class, 'importCommit']
                )->whereNumber('batch')
                ->name('santri.import.commit');


                // ================= CRUD =================

                Route::resource('santri',
                    \App\Http\Controllers\Tenant\SantriController::class
                );

                Route::get('santri-trash',
                    [\App\Http\Controllers\Tenant\SantriController::class, 'trash']
                )->name('santri.trash');

                Route::patch('santri/{id}/restore',
                    [\App\Http\Controllers\Tenant\SantriController::class, 'restore']
                )->name('santri.restore');
            });


            /*
            |--------------------------------------------------------------------------
            | WALI MODULE
            |--------------------------------------------------------------------------
            */
            Route::middleware('permission:manage_wali')->group(function () {

                Route::resource('wali',
                    \App\Http\Controllers\Tenant\WaliController::class
                );

                Route::get('wali-trash',
                    [\App\Http\Controllers\Tenant\WaliController::class, 'trash']
                )->name('wali.trash');

                Route::patch('wali/{id}/restore',
                    [\App\Http\Controllers\Tenant\WaliController::class, 'restore']
                )->name('wali.restore');

                Route::post('wali/ajax-store',
                    [\App\Http\Controllers\Tenant\WaliController::class, 'ajaxStore']
                )->name('wali.ajax.store');
            });

        });

});
