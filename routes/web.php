<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\SetoranController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\CetakController;
use App\Http\Controllers\SiswaImportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KreditController;
use App\Http\Controllers\TahunAjaranController;

use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// Auth routes (dari Breeze)
require __DIR__ . '/auth.php';

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/siswa/generate-id', function (\Illuminate\Http\Request $request) {
        $jenjang = strtoupper($request->input('jenjang', 'SD'));
        return response()->json([
            'id_siswa' => \App\Models\Siswa::generateIdSiswa($jenjang),
        ]);
    })->name('siswa.generate-id');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Route Siswa (CRUD) ──────────────────────────────────────────
    Route::resource('siswa', SiswaController::class)->middleware(['auth']);

    // ── Route Import Siswa (halaman terpisah) ───────────────────────
    Route::prefix('siswa-import')->name('siswa.import.')->middleware(['auth'])->group(function () {
        Route::get('/',           [SiswaImportController::class, 'index'])->name('index');
        Route::post('/',          [SiswaImportController::class, 'import'])->name('store');
        Route::get('/template',   [SiswaImportController::class, 'downloadTemplate'])->name('template');
    });
    // === PEMBAYARAN ===
    Route::get('/siswa/{siswa}/data', [PembayaranController::class, 'getSiswaData']);
    Route::resource('pembayaran', PembayaranController::class);

    // === SETORAN ===
    Route::resource('setoran', SetoranController::class);
    Route::get('/setoran/{setoran}/cetak', [SetoranController::class, 'cetak'])->name('setoran.cetak');

    // === LAPORAN ===
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');

    // === CETAK KARTU ===
    Route::get('/cetak', [CetakController::class, 'index'])->name('cetak.index');
    Route::post('/cetak/kartu', [CetakController::class, 'kartu'])->name('cetak.kartu');

    // === ADMIN YAYASAN ONLY ===
    Route::prefix('admin/users')
        ->name('admin.users.')
        ->middleware(['auth', 'role:admin_yayasan'])
        ->group(function () {
            Route::get('/',    [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/',   [UserController::class, 'store'])->name('store');
            Route::get('/{user}',      [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}',      [UserController::class, 'update'])->name('update');
            Route::delete('/{user}',   [UserController::class, 'destroy'])->name('destroy');

            // ✅ Route ini yang hilang saat pakai Route::resource
            Route::post(
                '/{user}/reset-password',
                [UserController::class, 'resetPassword']
            )->name('reset-password');
        });
    Route::get('/setting', [App\Http\Controllers\SettingController::class, 'index'])
        ->name('setting.index');

    Route::put('/setting', [App\Http\Controllers\SettingController::class, 'update'])
        ->name('setting.update');

    // ── Kredit Siswa ──────────────────────────────────────────────────
    Route::get('/kredit',                  [KreditController::class, 'index'])->name('kredit.index');
    Route::get('/kredit/{siswa}/tambah',   [KreditController::class, 'create'])->name('kredit.create');
    Route::post('/kredit/{siswa}',         [KreditController::class, 'store'])->name('kredit.store');

    // ── Preview AJAX (opsional, untuk kredit real-time) ───────────────
    Route::get('/pembayaran/preview', [App\Http\Controllers\PembayaranController::class, 'preview'])
        ->name('pembayaran.preview');

        
});
