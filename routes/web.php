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

                Route::resource('santri',
                    \App\Http\Controllers\Tenant\SantriController::class
                );

                Route::post('santri/{id}/restore',
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
            });

        });

});
