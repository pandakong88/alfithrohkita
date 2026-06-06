<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Public\SantriHandbookController;
use Illuminate\Support\Facades\Route;

Route::get('/{pondok_slug}/pedoman-santri', [SantriHandbookController::class, 'index'])->name('public.handbook.index');
Route::get('/{pondok_slug}/pedoman-santri/{handbook}/download', [SantriHandbookController::class, 'download'])->name('public.handbook.download');
Route::get('/{pondok_slug}/pedoman-santri/{handbook}/preview', [SantriHandbookController::class, 'preview'])->name('handbook.preview');
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

                 // ===================GABUT===================
            Route::resource('handbook', 
                 \App\Http\Controllers\Tenant\SantriHandbookController::class
             )->names('santri.handbook');

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

                Route::get('/santri/import/template',
                    [\App\Http\Controllers\Tenant\SantriController::class,'downloadTemplate']
                )->name('santri.template.download');
                
                Route::get('santri/import',
                    [\App\Http\Controllers\Tenant\SantriController::class, 'importForm']
                )->name('santri.import');

                Route::post('santri/import/preview',
                    [\App\Http\Controllers\Tenant\SantriController::class, 'previewImport']
                )->name('santri.import.preview');

                Route::get('santri/import/{batch}/preview',
                    [\App\Http\Controllers\Tenant\SantriController::class, 'showPreview']
                )->whereNumber('batch')
                ->name('santri.import.preview.show');

                Route::post('santri/import/{batch}/commit',
                    [\App\Http\Controllers\Tenant\SantriController::class, 'importCommit']
                )->whereNumber('batch')
                ->name('santri.import.commit');

                Route::get('santri/import/history',
                    [\App\Http\Controllers\Tenant\SantriController::class, 'importHistory']
                )->name('santri.import.history');


                Route::get('santri/snapshot/import',
                    [\App\Http\Controllers\Tenant\SantriSnapshotController::class, 'importForm']
                )->name('santri.snapshot.import');

                Route::post('santri/snapshot/preview',
                    [\App\Http\Controllers\Tenant\SantriSnapshotController::class, 'preview']
                )->name('santri.snapshot.preview');

                Route::post('santri/snapshot/{batch}/commit',
                    [\App\Http\Controllers\Tenant\SantriSnapshotController::class, 'commit']
                )->name('santri.snapshot.commit');
                
                Route::get('santri/snapshot/{batch}/preview',
                    [\App\Http\Controllers\Tenant\SantriSnapshotController::class, 'showPreview']
                )->name('santri.snapshot.preview.show');

                Route::post('santri/snapshot/{batch}/commit',
                    [\App\Http\Controllers\Tenant\SantriSnapshotController::class, 'commit']
                )->name('santri.snapshot.commit');



                

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

                // ================= Import =================
                Route::get('wali/import/template',
                    [\App\Http\Controllers\Tenant\WaliController::class, 'downloadTemplate']
                )->name('wali.template.download');
                
                Route::get('wali/import',
                    [\App\Http\Controllers\Tenant\WaliController::class, 'importForm']
                )->name('wali.import');

                Route::post('wali/import/preview',
                    [\App\Http\Controllers\Tenant\WaliController::class, 'previewImport']
                )->name('wali.import.preview');

                Route::get('wali/import/{batch}/preview',
                    [\App\Http\Controllers\Tenant\WaliController::class, 'showPreview']
                )->whereNumber('batch')
                ->name('wali.import.preview.show');

                Route::post('wali/import/{batch}/commit',
                    [\App\Http\Controllers\Tenant\WaliController::class, 'importCommit']
                )->whereNumber('batch')
                ->name('wali.import.commit');

                Route::get('wali/import/history',
                    [\App\Http\Controllers\Tenant\WaliController::class, 'importHistory']
                )->name('wali.import.history');

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

            /*
            |--------------------------------------------------------------------------
            | PERIZINAN MODULE
            |--------------------------------------------------------------------------
            */
            Route::middleware('permission:manage_perizinan')
            ->prefix('template-perizinan')
            ->name('template-perizinan.')
            ->group(function () {
        
                // upload PDF
                Route::get('/upload', [
                    App\Http\Controllers\Tenant\Perizinan\TemplatePerizinanController::class, 'create'
                ])->name('upload');

                Route::post('/upload', [
                    App\Http\Controllers\Tenant\Perizinan\TemplatePerizinanController::class, 'storeFile'
                ])->name('upload.store');

                Route::post('/store', [
                    App\Http\Controllers\Tenant\Perizinan\TemplatePerizinanController::class, 'store'
                ])->name('store');

                Route::delete('/{id}', [
                    \App\Http\Controllers\Tenant\Perizinan\TemplatePerizinanController::class, 'destroy'
                ])->name('destroy');

                Route::post('/{id}/restore', [
                    \App\Http\Controllers\Tenant\Perizinan\TemplatePerizinanController::class, 'restore'
                ])->name('restore');

                Route::delete('/{id}/force', [
                    \App\Http\Controllers\Tenant\Perizinan\TemplatePerizinanController::class, 'forceDelete'
                ])->name('forceDelete');

                Route::post('/update-status', [
                    \App\Http\Controllers\Tenant\Perizinan\TemplatePerizinanController::class, 'updateStatus'
                ])->name('update-status');

                // resource (optional, bisa tetap)
                Route::resource('/', App\Http\Controllers\Tenant\Perizinan\TemplatePerizinanController::class)
                    ->parameters(['' => 'template_perizinan']);
            });

            Route::middleware('permission:manage_perizinan')
            ->prefix('perizinan')
            ->name('perizinan.')
            ->group(function () {
                Route::get('perizinan/{id}', [
                    \App\Http\Controllers\Tenant\Perizinan\PerizinanController::class, 'show'
                ])->name('show');
                Route::get('perizinan/{id}', [
                    \App\Http\Controllers\Tenant\Perizinan\PerizinanController::class, 'show'
                ])->name('show');

                Route::get('/santri-data/{id}', [
                    \App\Http\Controllers\Tenant\Perizinan\PerizinanController::class, 'getSantriData'
                ])->name('santri-data');
                
                Route::get('perizinan/scan/{kode}', [
                    \App\Http\Controllers\Tenant\Perizinan\PerizinanController::class, 'scan'
                ])->name('scan');

                Route::post('perizinan/{id}/kembali', [
                    \App\Http\Controllers\Tenant\Perizinan\PerizinanController::class, 'kembali'
                ])->name('kembali');
                
                // Ganti dari POST ke GET dan tambahkan parameter ID
                Route::get('data-riwayat/{santri_id}', [
                    \App\Http\Controllers\Tenant\Perizinan\PerizinanController::class, 'dataRiwayat'
                ])->name('data-riwayat');

                // resource untuk manajemen perizinan (CRUD izin keluar masuk)
                Route::resource('/', \App\Http\Controllers\Tenant\Perizinan\PerizinanController::class);
            });

            /*
            |--------------------------------------------------------------------------
            | ABSENSI MODULE
            |--------------------------------------------------------------------------
            */
            
            // 1. MASTER SESI ABSENSI (Mirip Template Perizinan)
            Route::middleware('permission:manage_absensi')
            ->prefix('absensi-sesi')
            ->name('absensi-sesi.')
            ->group(function () {
                
                Route::post('/store', [
                    \App\Http\Controllers\Tenant\Absensi\AbsensiSesiController::class, 'store'
                ])->name('store');

                Route::delete('/{id}', [
                    \App\Http\Controllers\Tenant\Absensi\AbsensiSesiController::class, 'destroy'
                ])->name('destroy');

                Route::post('/{id}/restore', [
                    \App\Http\Controllers\Tenant\Absensi\AbsensiSesiController::class, 'restore'
                ])->name('restore');

                Route::delete('/{id}/force', [
                    \App\Http\Controllers\Tenant\Absensi\AbsensiSesiController::class, 'forceDelete'
                ])->name('forceDelete');

                // Resource untuk Master Sesi (Index, Create, Edit, dll)
                Route::resource('/', \App\Http\Controllers\Tenant\Absensi\AbsensiSesiController::class)
                    ->parameters(['' => 'absensi_sesi']);
                
                Route::get('/{id}/manage', [
                    \App\Http\Controllers\Tenant\Absensi\AbsensiSesiController::class, 'manageSantri'
                ])->name('manage');
                    
                Route::post('/{id}/manage', [
                    \App\Http\Controllers\Tenant\Absensi\AbsensiSesiController::class, 'updateSantri'
                ])->name('update-santri');

                Route::get('/{id}/print-fisik', [
                    \App\Http\Controllers\Tenant\Absensi\AbsensiSesiController::class, 'printAbsenFisik'
                ])->name('print-fisik');

                // Halaman Setting (Layout Dashboard)
                Route::get('/{id}/manage-print', [
                    \App\Http\Controllers\Tenant\Absensi\AbsensiSesiController::class, 'managePrint'
                ])->name('manage-print');
            });

            // 2. INPUT & REKAP ABSENSI
            Route::middleware('permission:manage_absensi')
            ->prefix('absensi')
            ->name('absensi.')
            ->group(function () {
                
                // 1. Gerbang Utama
                Route::get('/pilih-sesi', [
                    \App\Http\Controllers\Tenant\Absensi\AbsensiController::class, 'pilihSesi'
                ])->name('pilih-sesi');
        
                // 2. Input Sesi Spesifik (Manual/Scan)
                // Pindahkan ke '/sesi/{sesi_id}' agar tidak tabrakan dengan ID resource
                Route::get('/sesi/{sesi_id}', [
                    \App\Http\Controllers\Tenant\Absensi\AbsensiController::class, 'index'
                ])->name('index');
        
                // 3. Simpan Batch (Otok Satu)
                Route::post('/rekap-store', [
                    \App\Http\Controllers\Tenant\Absensi\AbsensiController::class, 'store'
                ])->name('store');
                
                Route::get('/print', [
                    \App\Http\Controllers\Tenant\Absensi\AbsensiController::class, 'print'
                ])->name('print');

                // 4. Ajax Riwayat
                Route::get('/data-riwayat/{santri_id}', [
                    \App\Http\Controllers\Tenant\Absensi\AbsensiController::class, 'dataRiwayat'
                ])->name('data-riwayat');

            });

            /*
            |--------------------------------------------------------------------------
            | PELANGGARAN & KESISWAAN MODULE
            |--------------------------------------------------------------------------
            */
            
            // MASTER KATEGORI PELANGGARAN
            Route::middleware('permission:manage_pelanggaran') // Sesuaikan nama permission kesiswaanmu
            ->prefix('kategori-pelanggaran')
            ->name('kategori-pelanggaran.')
            ->group(function () {
                
                // Index (Daftar Kategori Pelanggaran)
                Route::get('/', [
                    \App\Http\Controllers\Tenant\Pelanggaran\KategoriPelanggaranController::class, 'index'
                ])->name('index');

                // Store & Update (Dual action dijadikan satu endpoint POST)
                Route::post('/store', [
                    \App\Http\Controllers\Tenant\Pelanggaran\KategoriPelanggaranController::class, 'store'
                ])->name('store');

                // Delete Kategori
                Route::delete('/{id}', [
                    \App\Http\Controllers\Tenant\Pelanggaran\KategoriPelanggaranController::class, 'destroy'
                ])->name('destroy');

                /** 
                 * NB: Jika nanti sistem pelanggaranmu butuh fitur Soft Deletes (Restore & Force Delete) 
                 * seperti absensi sesi, kamu tinggal uncomment baris di bawah ini:
                 */
                /*
                Route::post('/{id}/restore', [
                    \App\Http\Controllers\Tenant\Pelanggaran\KategoriPelanggaranController::class, 'restore'
                ])->name('restore');

                Route::delete('/{id}/force', [
                    \App\Http\Controllers\Tenant\Pelanggaran\KategoriPelanggaranController::class, 'forceDelete'
                ])->name('forceDelete');
                */
            });

            // 2. CORE PENCATATAN PELANGGARAN SANTRI
            Route::middleware('permission:manage_pelanggaran')
            ->prefix('pelanggaran')
            ->name('pelanggaran.')
            ->group(function () {
                
                // Halaman utama riwayat & form pencatatan
                Route::get('/', [
                    \App\Http\Controllers\Tenant\Pelanggaran\PelanggaranSantriController::class, 'index'
                ])->name('index');

                // Simpan pencatatan (Tunggal maupun Massal hasil sebaran DTO)
                Route::post('/store', [
                    \App\Http\Controllers\Tenant\Pelanggaran\PelanggaranSantriController::class, 'store'
                ])->name('store');

                // Update detail pelanggaran per baris data
                Route::put('/{id}', [
                    \App\Http\Controllers\Tenant\Pelanggaran\PelanggaranSantriController::class, 'update'
                ])->name('update');

                // Batalkan/Hapus pencatatan pelanggaran
                Route::delete('/{id}', [
                    \App\Http\Controllers\Tenant\Pelanggaran\PelanggaranSantriController::class, 'destroy'
                ])->name('destroy');
            });

             /*
            |--------------------------------------------------------------------------
            | IMPORT TEMPLATE MANAGEMENT
            |--------------------------------------------------------------------------
            */
            Route::post('custom-fields', [
                App\Http\Controllers\Tenant\ImportTemplateController::class, 'storeCustomField'
            ])->name('custom-fields.store');
            
            Route::delete('custom-fields/{id}', [
                App\Http\Controllers\Tenant\ImportTemplateController::class, 'destroyCustomField'
            ])->name('custom-fields.destroy');

            Route::get('import-templates/{id}/edit', [
                App\Http\Controllers\Tenant\ImportTemplateController::class, 'edit'
            ])->name('import-templates.edit');

            Route::put('import-templates/{id}', [
                App\Http\Controllers\Tenant\ImportTemplateController::class, 'update'
            ])->name('import-templates.update');

            Route::resource('import-templates',
                \App\Http\Controllers\Tenant\ImportTemplateController::class
            )->names('import-templates');

            Route::get('import-templates/{id}/download',
                [\App\Http\Controllers\Tenant\ImportTemplateController::class,'download']
            )->name('import-templates.download');

            Route::post('import-survey',
                [\App\Http\Controllers\Tenant\ImportTemplateController::class,'import']
            )->name('import-survey');

            /*
            |--------------------------------------------------------------------------
            | IMPORT PROCESS
            |--------------------------------------------------------------------------
            */
            Route::get('/upload', 
                [\App\Http\Controllers\Tenant\ImportController::class, 'index']
            )->name('import.upload');
            
            Route::post('/preview', 
                [\App\Http\Controllers\Tenant\ImportController::class, 'preview']
            )->name('import.preview');
            
            Route::get('/history', 
                [\App\Http\Controllers\Tenant\ImportController::class,'history']
            )->name('import.history');

            Route::get('/history/{batch}', 
                [\App\Http\Controllers\Tenant\ImportController::class,'detail']
            )->name('import.detail');


            Route::get('/{batch}', 
                [\App\Http\Controllers\Tenant\ImportController::class, 'show']
            )->name('import.show');

            Route::post('/{batch}/commit', 
                [\App\Http\Controllers\Tenant\ImportController::class, 'commit']
            )->name('import.commit');
          
            Route::get('/import/{batch}/errors/download', 
                [\App\Http\Controllers\Tenant\ImportController::class,'downloadErrors']
            )->name('import.errors.download');
            
            Route::post('/import/{batch}/rollback',
                [\App\Http\Controllers\Tenant\ImportController::class,'rollback']
            )->name('import.rollback');


            });

});
