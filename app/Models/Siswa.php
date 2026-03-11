<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Siswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'siswa';

    protected $fillable = [
        'id_siswa',
        'nama',
        'jenjang',
        'saldo_kredit',
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
        'keterangan',
        'no_hp_wali',
        // DIHAPUS: 'kelas', 'nominal_pembayaran', 'nominal_donator', 'nominal_mamin'
        // Nominal SPP kini ada di tabel siswa_kelas (per tahun pelajaran)
    ];

    protected $casts = [
        'tanggal_masuk'  => 'date',
        'tanggal_keluar' => 'date',
        'saldo_kredit'   => 'decimal:2',
        // DIHAPUS: nominal_pembayaran, nominal_donator, nominal_mamin
    ];

    // ─── Relasi ─────────────────────────────────────────────────────

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    /**
     * Riwayat penempatan kelas + tarif SPP per tahun pelajaran.
     * Dari sini bisa diambil nominal_spp, nominal_donator, nominal_mamin
     * yang berlaku untuk masing-masing tahun.
     */
    public function siswaKelas()
    {
        return $this->hasMany(SiswaKelas::class);
    }

    /**
     * Record siswa_kelas untuk tahun pelajaran yang sedang aktif.
     */
    public function kelasAktif()
    {
        return $this->hasOne(SiswaKelas::class)
            ->whereHas('tahunPelajaran', fn($q) => $q->where('is_active', true));
    }

    /**
     * Detail bulan yang sudah dibayar (via tabel pembayaran_bulan).
     */
    public function pembayaranBulan()
    {
        return $this->hasMany(PembayaranBulan::class);
    }

    /**
     * Riwayat kredit siswa.
     */
    public function kreditLog()
    {
        return $this->hasMany(KreditLog::class)->latest();
    }

    // ─── Accessors ──────────────────────────────────────────────────

    /**
     * Tagihan bersih per bulan berdasarkan siswa_kelas tahun aktif.
     * Return 0 jika siswa belum memiliki record siswa_kelas aktif.
     */
    public function getTotalTagihanAttribute(): float
    {
        $sk = $this->kelasAktif;
        if (!$sk) return 0.0;

        return max(0, (float) $sk->nominal_spp
            - (float) $sk->nominal_donator
            + (float) $sk->nominal_mamin);
    }

    /**
     * Daftar bulan yang sudah dibayar siswa ini (format: "YYYY-MM").
     * Menggunakan tabel pembayaran_bulan, bukan JSON di tabel pembayaran.
     */
    public function getBulanSudahBayar(?int $tahunPelajaranId = null): array
    {
        $query = $this->pembayaranBulan();

        if ($tahunPelajaranId) {
            $query->whereHas('pembayaran', fn($q) =>
                $q->where('tahun_pelajaran_id', $tahunPelajaranId)
            );
        }

        return $query->pluck('bulan')->unique()->values()->toArray();
    }

    /**
     * Hitung daftar bulan aktif siswa dalam satu tahun ajaran (Jul–Jun).
     */
    public function getBulanAktif(int $tahunAjaran): array
    {
        $bulanList   = [];
        $urutanBulan = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];

        foreach ($urutanBulan as $bulan) {
            $tahun   = ($bulan >= 7) ? $tahunAjaran : $tahunAjaran + 1;
            $periode = sprintf('%04d-%02d', $tahun, $bulan);

            $tanggalPeriode = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();

            if ($tanggalPeriode->lt($this->tanggal_masuk->copy()->startOfMonth())) continue;
            if ($this->tanggal_keluar && $tanggalPeriode->gt($this->tanggal_keluar->copy()->startOfMonth())) continue;

            $bulanList[] = $periode;
        }

        return $bulanList;
    }

    // ─── Scopes ─────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeJenjang($query, string $jenjang)
    {
        return $query->where('jenjang', $jenjang);
    }

    // ─── Kredit Helpers ─────────────────────────────────────────────

    /**
     * Tambah saldo kredit dan catat di log.
     */
    public function tambahKredit(float $jumlah, string $keterangan = '', ?int $userId = null): KreditLog
    {
        return DB::transaction(function () use ($jumlah, $keterangan, $userId) {
            $this->refresh();

            $sebelum = (float) $this->saldo_kredit;
            $sesudah = $sebelum + $jumlah;

            $this->increment('saldo_kredit', $jumlah);

            return KreditLog::create([
                'siswa_id'      => $this->id,
                'user_id'       => $userId ?? Auth::id(),
                'pembayaran_id' => null,
                'tipe'          => 'tambah',   // enum: tambah | pakai
                'jumlah'        => $jumlah,
                'saldo_sebelum' => $sebelum,
                'saldo_sesudah' => $sesudah,
                'keterangan'    => $keterangan,
            ]);
        });
    }

    /**
     * Pakai saldo kredit saat input pembayaran.
     * Mengembalikan jumlah yang benar-benar dipakai.
     */
    public function pakaiKredit(float $jumlah, int $pembayaranId, string $keterangan = '', ?int $userId = null): float
    {
        return DB::transaction(function () use ($jumlah, $pembayaranId, $keterangan, $userId) {
            $this->refresh();

            $dipakai = min($jumlah, (float) $this->saldo_kredit);
            if ($dipakai <= 0) return 0.0;

            $sebelum = (float) $this->saldo_kredit;
            $sesudah = $sebelum - $dipakai;

            $this->decrement('saldo_kredit', $dipakai);

            KreditLog::create([
                'siswa_id'      => $this->id,
                'user_id'       => $userId ?? Auth::id(),
                'pembayaran_id' => $pembayaranId,
                'tipe'          => 'pakai',    // enum: tambah | pakai
                'jumlah'        => $dipakai,
                'saldo_sebelum' => $sebelum,
                'saldo_sesudah' => $sesudah,
                'keterangan'    => $keterangan ?: 'Pemotongan otomatis dari pembayaran',
            ]);

            return $dipakai;
        });
    }

    // ─── Static Helpers ─────────────────────────────────────────────

    public static function generateIdSiswa(string $jenjang): string
    {
        $prefix = match (strtoupper($jenjang)) {
            'TK'    => 'TK',
            'SD'    => 'SD',
            'SMP'   => 'SM',
            default => 'XX',
        };

        // withTrashed() agar ID soft-deleted tetap dihitung, mencegah duplikasi
        $lastSiswa = static::withTrashed()
            ->where('id_siswa', 'like', $prefix . '%')
            ->orderBy('id_siswa', 'desc')
            ->first();

        $newNum = $lastSiswa
            ? ((int) substr($lastSiswa->id_siswa, strlen($prefix))) + 1
            : 1;

        return $prefix . str_pad($newNum, 4, '0', STR_PAD_LEFT);
    }

    public function getKelasForTahun(?TahunPelajaran $tahunPelajaran = null): ?\App\Models\SiswaKelas
{
    if ($tahunPelajaran === null) {
        // Fallback: gunakan kelasAktif (sudah ada di model)
        return $this->relationLoaded('kelasAktif')
            ? $this->kelasAktif
            : $this->kelasAktif()->with('kelas')->first();
    }

    // Cari dari relasi siswaKelas yang sudah di-eager-load (jika ada)
    if ($this->relationLoaded('siswaKelas')) {
        return $this->siswaKelas
            ->where('tahun_pelajaran_id', $tahunPelajaran->id)
            ->first();
    }

    // Fallback: query langsung
    return $this->siswaKelas()
        ->where('tahun_pelajaran_id', $tahunPelajaran->id)
        ->with('kelas')
        ->first();
}
}