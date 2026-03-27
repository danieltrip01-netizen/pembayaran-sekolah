<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Setting;
use Illuminate\Support\Facades\URL;

class NotifikasiPembayaran
{
    public function __construct(
        private WhatsAppService $wa
    ) {}

    public function kirimSetelahBayar(Pembayaran $pembayaran): void
    {
        $pembayaran->loadMissing(['siswa', 'pembayaranBulan', 'user']);

        $siswa   = $pembayaran->siswa;
        $setting = Setting::global();
        $namaApp = $setting?->nama_yayasan ?: 'SPP';

        if (!empty($siswa->no_hp_wali)) {
            $this->wa->kirim($siswa->no_hp_wali, $this->templateWali($pembayaran, $namaApp));
        }

        $noAdmin = config('services.fonnte.admin_number', '');
        if ($noAdmin) {
            $this->wa->kirim($noAdmin, $this->templateAdmin($pembayaran, $namaApp));
        }
    }

    private function templateWali(Pembayaran $pembayaran, string $namaApp): string
    {
        $siswa      = $pembayaran->siswa;
        $bulanList  = $pembayaran->pembayaranBulan
                        ->pluck('bulan')
                        ->map(fn($b) => $this->formatBulan($b))
                        ->join(', ');
        $total      = 'Rp ' . number_format($pembayaran->total_bayar, 0, ',', '.');
        $tanggal    = $pembayaran->tanggal_bayar->translatedFormat('d F Y');
        $kode       = $pembayaran->kode_bayar;
        $namaSiswa  = $siswa->nama;

        // Signed URL — bisa diakses tanpa login, berlaku 7 hari
        $riwayatUrl = URL::temporarySignedRoute(
            'siswa.riwayat.publik',
            now()->addDays(7),
            ['siswa' => $siswa->id]
        );

        return "✅ *Konfirmasi Pembayaran SPP*\n\n"
             . "Yth. Wali Murid *{$namaSiswa}*,\n\n"
             . "Pembayaran SPP telah kami terima:\n"
             . "📅 Tanggal   : {$tanggal}\n"
             . "📚 Bulan     : {$bulanList}\n"
             . "💰 Jumlah    : *{$total}*\n"
             . "🔖 Kode Bayar: {$kode}\n\n"
             . "Lihat riwayat lengkap (berlaku 7 hari):\n"
             . "{$riwayatUrl}\n\n"
             . "Terima kasih 🙏\n"
             . "_{$namaApp}_";
    }

    private function templateAdmin(Pembayaran $pembayaran, string $namaApp): string
    {
        $siswa      = $pembayaran->siswa;
        $bulanList  = $pembayaran->pembayaranBulan
                        ->pluck('bulan')
                        ->map(fn($b) => $this->formatBulan($b))
                        ->join(', ');
        $total      = 'Rp ' . number_format($pembayaran->total_bayar, 0, ',', '.');
        $tanggal    = $pembayaran->tanggal_bayar->translatedFormat('d F Y');
        $namaSiswa  = $siswa->nama;
        $namaKelas  = $siswa->kelasAktif?->kelas?->nama ?? '-';
        $namaUser   = $pembayaran->user?->name ?? 'Sistem';

        return "🔔 *Transaksi Baru — {$namaApp}*\n\n"
             . "Siswa  : *{$namaSiswa}*\n"
             . "Kelas  : {$namaKelas}\n"
             . "Bulan  : {$bulanList}\n"
             . "Total  : *{$total}*\n"
             . "Tanggal: {$tanggal}\n"
             . "Petugas: {$namaUser}";
    }

    private function formatBulan(string $periode): string
    {
        [$tahun, $bln] = explode('-', $periode, 2);
        $nama = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
        ];
        return ($nama[$bln] ?? $bln) . ' ' . $tahun;
    }
}