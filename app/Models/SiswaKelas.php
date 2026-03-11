<?php

namespace App\Models;

use App\Traits\HasBulanLabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SiswaKelas extends Model
{
    use SoftDeletes, HasBulanLabel;

    protected $table = 'siswa_kelas';

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'tahun_pelajaran_id',
        'nominal_spp',
        'nominal_donator',
        'nominal_mamin',
    ];

    protected $casts = [
        'nominal_spp'     => 'decimal:2',
        'nominal_donator' => 'decimal:2',
        'nominal_mamin'   => 'decimal:2',
    ];

    // ─── Relasi ──────────────────────────────────────────────────────

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeTahunAktif($query)
    {
        // Lebih efisien dari whereHas karena menghindari subquery
        $tahunAktifId = TahunPelajaran::where('is_active', true)->value('id');

        return $tahunAktifId
            ? $query->where('tahun_pelajaran_id', $tahunAktifId)
            : $query->whereRaw('0 = 1');
    }

    public function scopeJenjang($query, string $jenjang)
    {
        return $query->whereHas('kelas', fn($q) => $q->where('jenjang', $jenjang));
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    /**
     * Tagihan bersih per bulan.
     * Rumus: (SPP − Donatur) + Mamin
     */
    public function getTagihanPerBulan(): float
    {
        return max(0.0,
            (float) $this->nominal_spp
            - (float) $this->nominal_donator
            + (float) $this->nominal_mamin
        );
    }

    // ─── Auto-Kredit ─────────────────────────────────────────────────

    /**
     * Hitung kredit yang harus digenerate akibat kenaikan donatur.
     *
     * Logika:
     *   - Jika donatur BARU > donatur LAMA, selisih × jumlah bulan yang
     *     SUDAH dibayar dalam tahun pelajaran ini = kredit terutang.
     *   - Kredit hanya digenerate jika siswa sudah pernah membayar
     *     di tahun pelajaran yang sama (bukan retroaktif ke 0 transaksi).
     *
     * @return array{
     *   eligible: bool,
     *   selisih_per_bulan: float,
     *   jumlah_bulan_dibayar: int,
     *   total_kredit: float,
     *   bulan_list: array<string>
     * }
     */
    public function hitungKreditOtomatis(float $donaturLama, float $donaturBaru): array
    {
        $selisih = $donaturBaru - $donaturLama;

        $base = [
            'eligible'             => false,
            'selisih_per_bulan'    => $selisih,
            'jumlah_bulan_dibayar' => 0,
            'total_kredit'         => 0.0,
            'bulan_list'           => [],
        ];

        // Tidak ada kenaikan donatur → tidak ada kredit
        if ($selisih <= 0) {
            return $base;
        }

        // Ambil bulan yang sudah dibayar siswa di tahun pelajaran ini
        $bulanDibayar = PembayaranBulan::whereHas('pembayaran', fn($q) =>
                $q->where('siswa_id', $this->siswa_id)
                  ->where('tahun_pelajaran_id', $this->tahun_pelajaran_id)
            )
            ->pluck('bulan')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $jumlahBulan = count($bulanDibayar);

        // Belum ada pembayaran di tahun ini → tidak eligible
        if ($jumlahBulan === 0) {
            return $base;
        }

        return [
            'eligible'             => true,
            'selisih_per_bulan'    => $selisih,
            'jumlah_bulan_dibayar' => $jumlahBulan,
            'total_kredit'         => $selisih * $jumlahBulan,
            'bulan_list'           => $bulanDibayar,
        ];
    }

    /**
     * Terapkan kredit otomatis jika donatur naik & siswa sudah pernah bayar.
     * Harus dipanggil di dalam DB::transaction() yang sudah ada di controller.
     *
     * @return KreditLog|null  KreditLog yang dibuat, atau null jika tidak eligible
     */
    public function terapkanKreditOtomatis(float $donaturLama, float $donaturBaru): ?KreditLog
    {
        $kalkulasi = $this->hitungKreditOtomatis($donaturLama, $donaturBaru);

        if (!$kalkulasi['eligible'] || $kalkulasi['total_kredit'] <= 0) {
            return null;
        }

        $siswa = $this->siswa ?? Siswa::findOrFail($this->siswa_id);

        return $siswa->tambahKredit(
            jumlah     : $kalkulasi['total_kredit'],
            keterangan : sprintf(
                'Kredit otomatis: kenaikan donatur Rp %s/bln × %d bln (%s)',
                number_format($kalkulasi['selisih_per_bulan'], 0, ',', '.'),
                $kalkulasi['jumlah_bulan_dibayar'],
                static::formatBulanList($kalkulasi['bulan_list'])
            ),
            userId: Auth::id(),
        );
    }

}