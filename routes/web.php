<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\SetoranController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\CetakController;
use App\Http\Controllers\SiswaImportController;
use App\Http\Controllers\SettingController;

use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// Auth routes (dari Breeze)
require __DIR__ . '/auth.php';

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/siswa/generate-id', function (\Illuminate\Http\Request $request) {
        $jenjang = strtoupper($request->get('jenjang', 'SD'));
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
    Route::middleware(['role:admin_yayasan'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class);
    });
    Route::get('/setting', [App\Http\Controllers\SettingController::class, 'index'])
        ->name('setting.index');

    Route::put('/setting', [App\Http\Controllers\SettingController::class, 'update'])
        ->name('setting.update');
});
