<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Siswa;
use App\Models\KreditLog;
use Illuminate\Http\Request;

class RiwayatPublikController extends Controller
{
    private const NAMA_BULAN = [
        '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
        '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
        '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
        '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
    ];

    public function show(Request $request, Siswa $siswa)
    {
        // ── Data sekolah ───────────────────────────────────────────
        // Semua data (nama_sekolah, nama_yayasan, logo) dibaca dari row jenjang,
        // konsisten dengan pola CetakController::buildSettingMaps().
        $setting = Setting::forJenjang($siswa->jenjang);

        $namaSekolah = $setting->nama_sekolah ?? ($siswa->jenjang . ' Kristen Dorkas');
        $namaYayasan = $setting->nama_yayasan ?? '';
        
        $logoUrl     = match(strtoupper($siswa->jenjang)) {
    'TK'  => asset('img/logo-tk.png'),
    'SD'  => asset('img/logo-sd.png'),
    'SMP' => asset('img/logo-smp.png'),
    default => null,
};

        // ── Kelas & tahun ajaran aktif ─────────────────────────────
        $kelasAktif = $siswa->kelasAktif()->with('kelas', 'tahunPelajaran')->first();
        $kelasNama  = $kelasAktif?->kelas?->nama ?? '-';
        $tahunObj   = $kelasAktif?->tahunPelajaran;
        $tahunNama  = $tahunObj?->nama ?? date('Y') . '/' . (date('Y') + 1);
        $tahunInt   = $tahunObj?->tahun_ajaran
                      ?? (now()->month >= 7 ? now()->year : now()->year - 1);

        // Nominal default dari siswa_kelas — hanya dipakai untuk bulan BELUM BAYAR
        $nominalSpp     = (float) ($kelasAktif?->nominal_spp     ?? 0);
        $nominalDonatur = (float) ($kelasAktif?->nominal_donator ?? 0);
        $nominalMamin   = (float) ($kelasAktif?->nominal_mamin   ?? 0);

        // ── Kredit per pembayaran dari kredit_log ──────────────────
        // kredit_log.tipe = 'pakai', di-index oleh pembayaran_id
        $kreditPerPembayaran = KreditLog::where('siswa_id', $siswa->id)
            ->where('tipe', 'pakai')
            ->whereNotNull('pembayaran_id')
            ->pluck('jumlah', 'pembayaran_id') // [pembayaran_id => jumlah]
            ->toArray();

        // ── Susun map periode → data bulan ────────────────────────
        // PENTING: PembayaranController::store() menyimpan nominal_mamin dan
        // nominal_donator sebagai TOTAL (× jumlah_bulan) di tabel pembayaran,
        // sedangkan tabel pembayaran_bulan hanya menyimpan kolom penanda bulan
        // (pembayaran_id, siswa_id, bulan) — kolom nominal TIDAK diisi.
        // Oleh karena itu kita harus membaca dari parent Pembayaran dan membagi
        // rata per bulan, bukan dari pembayaran_bulan yang selalu null.
        $periodeMap = [];

        foreach ($siswa->pembayaran()->with('pembayaranBulan')->get() as $p) {
            $jumlahBulan = $p->pembayaranBulan->count() ?: 1;

            // Nilai per bulan — diambil dari snapshot yang tersimpan di pembayaran
            $sppPerBulan     = (float) $p->nominal_per_bulan;
            $donaturPerBulan = (float) $p->nominal_donator / $jumlahBulan; // total ÷ bulan
            $maminPerBulan   = (float) $p->nominal_mamin   / $jumlahBulan; // total ÷ bulan

            // Kredit yang dipakai untuk transaksi ini (dibagi rata per bulan)
            $kreditTransaksi = (float) ($kreditPerPembayaran[$p->id] ?? 0);
            $kreditPerBulan  = $kreditTransaksi / $jumlahBulan;

            // yang_dibayar per bulan = total_bayar ÷ jumlah_bulan
            $yangDibayarPerBulan = (float) $p->total_bayar / $jumlahBulan;

            foreach ($p->pembayaranBulan as $pb) {
                $periodeMap[$pb->bulan] = [
                    'periode'      => $pb->bulan,
                    'sudah_bayar'  => true,
                    'tanggal'      => $p->tanggal_bayar?->translatedFormat('d M Y'),
                    'kode_bayar'   => $p->kode_bayar,
                    'spp'          => round($sppPerBulan, 2),
                    'donatur'      => round($donaturPerBulan, 2),
                    'mamin'        => round($maminPerBulan, 2),
                    'kredit'       => round($kreditPerBulan, 2),
                    'yang_dibayar' => round($yangDibayarPerBulan, 2),
                ];
            }
        }

        // ── Bangun 12 bulan penuh (Jul → Jun) ─────────────────────
        $riwayat = [];
        foreach ([7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6] as $m) {
            $tahun   = ($m >= 7) ? $tahunInt : ($tahunInt + 1);
            $periode = sprintf('%04d-%02d', $tahun, $m);
            $noStr   = sprintf('%02d', $m);

            // Default untuk bulan yang BELUM dibayar — pakai nominal siswa_kelas saat ini
            $default = [
                'periode'      => $periode,
                'no_str'       => $noStr,
                'nama_bulan'   => (self::NAMA_BULAN[$noStr] ?? $noStr) . ' ' . $tahun,
                'sudah_bayar'  => false,
                'tanggal'      => null,
                'kode_bayar'   => null,
                'spp'          => $nominalSpp,
                'donatur'      => $nominalDonatur,
                'mamin'        => $nominalMamin,
                'kredit'       => 0,
                'yang_dibayar' => 0,
            ];

            $entry = array_merge($default, $periodeMap[$periode] ?? []);

            // no_str & nama_bulan selalu ditulis ulang — tidak dari DB
            $entry['no_str']     = $noStr;
            $entry['nama_bulan'] = (self::NAMA_BULAN[$noStr] ?? $noStr) . ' ' . $tahun;

            $riwayat[] = $entry;
        }

        $totalLunas   = collect($riwayat)->where('sudah_bayar', true)->count();
        $totalTagihan = 12;

        return view('siswa.riwayat-publik', compact(
            'siswa', 'kelasNama', 'tahunNama',
            'namaSekolah', 'namaYayasan', 'logoUrl',
            'riwayat', 'totalLunas', 'totalTagihan',
        ));
    }
}