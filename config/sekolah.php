<?php

/**
 * Konfigurasi Nama & Identitas Sekolah
 * Sesuaikan nilai-nilai di bawah ini dengan data sekolah Anda.
 *
 * File: config/sekolah.php
 */

return [

    // ── Identitas Yayasan ────────────────────────────────────────
    'yayasan' => env('SEKOLAH_YAYASAN', 'Yayasan Pendidikan Kristen'),

    // ── Nama Sekolah per Jenjang ─────────────────────────────────
    'nama_tk'  => env('SEKOLAH_NAMA_TK',  'TK Kristen'),
    'nama_sd'  => env('SEKOLAH_NAMA_SD',  'SD Kristen'),
    'nama_smp' => env('SEKOLAH_NAMA_SMP', 'SMP Kristen'),

    // ── Alamat (bisa beda per jenjang, atau pakai satu alamat) ───
    'alamat'     => env('SEKOLAH_ALAMAT', ''),
    'alamat_tk'  => env('SEKOLAH_ALAMAT_TK',  env('SEKOLAH_ALAMAT', '')),
    'alamat_sd'  => env('SEKOLAH_ALAMAT_SD',  env('SEKOLAH_ALAMAT', '')),
    'alamat_smp' => env('SEKOLAH_ALAMAT_SMP', env('SEKOLAH_ALAMAT', '')),

    // ── Kota (digunakan di footer tanda tangan kartu) ────────────
    'kota' => env('SEKOLAH_KOTA', 'Lasem'),

    // ── Kepala Sekolah per Jenjang ───────────────────────────────
    'kepsek_tk'  => env('KEPSEK_TK',  'Kepala Sekolah TK'),
    'kepsek_sd'  => env('KEPSEK_SD',  'Kepala Sekolah SD'),
    'kepsek_smp' => env('KEPSEK_SMP', 'Kepala Sekolah SMP'),

];