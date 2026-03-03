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
    use HasFactory;
    use SoftDeletes;

    protected $table = 'siswa';

    protected $fillable = [
        'id_siswa',
        'nama',
        'kelas',
        'jenjang',
        'nominal_pembayaran',
        'nominal_donator',
        'nominal_mamin',
        'saldo_kredit',          // ← TAMBAH: agar mass-assignable jika perlu
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_masuk'      => 'date',
        'tanggal_keluar'     => 'date',
        'nominal_pembayaran' => 'decimal:2',
        'nominal_donator'    => 'decimal:2',
        'nominal_mamin'      => 'decimal:2',
        'saldo_kredit'       => 'integer',   // ← TAMBAH: selalu integer, tidak ada desimal
    ];

    // ─── Relasi ─────────────────────────────────────────────────────

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    /**
     * Relasi ke riwayat kredit siswa.
     * ← TAMBAH: dibutuhkan oleh KreditController::create()
     */
    public function kreditLog()
    {
        return $this->hasMany(KreditLog::class)->latest();
    }

    // ─── Accessors ──────────────────────────────────────────────────

    /**
     * Total tagihan bersih per bulan: (SPP - Donatur + Mamin)
     */
    public function getTotalTagihanAttribute(): float
    {
        return (float) $this->nominal_pembayaran
            - (float) $this->nominal_donator
            + (float) $this->nominal_mamin;
    }

    /**
     * Selalu return array, tidak pernah null.
     */
    public function getBulanSudahBayarAttribute(int $tahunpelajaranid): array
    {
        if (!$this->relationLoaded('pembayaran')) {
            $this->load('pembayaran');
        }

        $result = [];
        foreach ($this->pembayaran as $bayar) {
            $raw   = $bayar->getRawOriginal('bulan_bayar');
            $bulan = static::safeDecode($raw);
            foreach ($bulan as $b) {
                if (!empty($b) && is_string($b)) $result[] = $b;
            }
        }

        return array_values(array_unique($result));
    }
    /**
     * Decode bulan_bayar dengan aman dari segala format.
     */
    public static function safeDecode(mixed $value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && !empty($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) return $decoded;
        }
        return [];
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
     * Dibungkus transaction agar atomik.
     */
    public function tambahKredit(int $jumlah, string $keterangan = '', ?int $userId = null): KreditLog
    {
        return DB::transaction(function () use ($jumlah, $keterangan, $userId) {
            // Refresh agar dapat nilai terkini
            $this->refresh();

            $sebelum = (int) $this->saldo_kredit;
            $sesudah = $sebelum + $jumlah;

            $this->increment('saldo_kredit', $jumlah);

            return KreditLog::create([
                'siswa_id'      => $this->id,
                'user_id'       => $userId ?? Auth::id(),
                'pembayaran_id' => null,
                'tipe'          => 'tambah',
                'jumlah'        => $jumlah,
                'saldo_sebelum' => $sebelum,
                'saldo_sesudah' => $sesudah,
                'keterangan'    => $keterangan,
            ]);
        });
    }

    /**
     * Pakai saldo kredit (dipanggil saat input pembayaran).
     * Mengembalikan jumlah yang benar-benar dipakai.
     */
    public function pakaiKredit(int $jumlah, int $pembayaranId, string $keterangan = '', ?int $userId = null): int
    {
        return DB::transaction(function () use ($jumlah, $pembayaranId, $keterangan, $userId) {
            // Refresh agar dapat nilai terkini (hindari race condition)
            $this->refresh();

            $dipakai = min($jumlah, (int) $this->saldo_kredit);
            if ($dipakai <= 0) return 0;

            $sebelum = (int) $this->saldo_kredit;
            $sesudah = $sebelum - $dipakai;

            $this->decrement('saldo_kredit', $dipakai);

            KreditLog::create([
                'siswa_id'      => $this->id,
                'user_id'       => $userId ?? Auth::id(),
                'pembayaran_id' => $pembayaranId,
                'tipe'          => 'pakai',
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

        $lastSiswa = static::where('id_siswa', 'like', $prefix . '%')
            ->orderBy('id_siswa', 'desc')
            ->first();

        $newNum = $lastSiswa
            ? ((int) substr($lastSiswa->id_siswa, strlen($prefix))) + 1
            : 1;

        return $prefix . str_pad($newNum, 4, '0', STR_PAD_LEFT);
    }
}
