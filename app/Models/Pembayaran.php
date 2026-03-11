<?php

namespace App\Models;

use App\Traits\HasBulanLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use HasFactory, SoftDeletes, HasBulanLabel;

    protected $table = 'pembayaran';

    protected $fillable = [
        'kode_bayar',
        'siswa_id',
        'setoran_id',
        'tahun_pelajaran_id',
        'siswa_kelas_id',
        'user_id',
        'tanggal_bayar',
        'jumlah_bulan',
        'nominal_per_bulan',
        'nominal_mamin',
        'nominal_donator',
        'kredit_digunakan',
        'total_bayar',
        'status',
        'sisa_tagihan',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_bayar'     => 'date',
        'nominal_per_bulan' => 'decimal:2',
        'nominal_mamin'     => 'decimal:2',
        'nominal_donator'   => 'decimal:2',
        'kredit_digunakan'  => 'decimal:2',
        'total_bayar'       => 'decimal:2',
        'sisa_tagihan'      => 'decimal:2',
        'jumlah_bulan'      => 'integer',
    ];

    // ─── Relasi ──────────────────────────────────────────────────────────────

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function setoran()
    {
        return $this->belongsTo(Setoran::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    /**
     * Snapshot siswa_kelas saat transaksi.
     * Nullable — jika record siswa_kelas dihapus, histori tetap aman
     * karena nominal sudah ter-snapshot di kolom nominal_per_bulan, dst.
     */
    public function siswaKelas()
    {
        return $this->belongsTo(SiswaKelas::class);
    }

    /**
     * Detail bulan yang dibayar — menggantikan kolom bulan_bayar (JSON).
     */
    public function pembayaranBulan()
    {
        return $this->hasMany(PembayaranBulan::class);
    }

    public function kreditLog()
    {
        return $this->hasMany(KreditLog::class);
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    /**
     * Label bulan yang terbaca manusia, misal "Jul 2024, Agu 2024".
     * Menggunakan relasi pembayaran_bulan.
     */
    public function getBulanLabelAttribute(): string
    {
        $bulanList = $this->relationLoaded('pembayaranBulan')
            ? $this->pembayaranBulan
            : $this->pembayaranBulan()->orderBy('bulan')->get();

        if ($bulanList->isEmpty()) {
            return '-';
        }

        return $bulanList->map(fn($pb) => static::formatBulan($pb->bulan))->implode(', ');
    }

    /**
     * Tagihan bruto sebelum potongan kredit.
     */
    public function getTagihanBrutoAttribute(): float
    {
        return (float) $this->total_bayar + (float) ($this->kredit_digunakan ?? 0);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeBelumSetor($query)
    {
        return $query->whereNull('setoran_id');
    }

    public function scopeSudahSetor($query)
    {
        return $query->whereNotNull('setoran_id');
    }

    public function scopeTahunPelajaran($query, int $tahunPelajaranId)
    {
        return $query->where('tahun_pelajaran_id', $tahunPelajaranId);
    }

    // ─── Static Helpers ──────────────────────────────────────────────────────

    /**
     * Generate kode bayar unik, aman untuk concurrent request.
     * Format: PAY-YYYYMMDD-XXXX
     */
    public static function generateKodeBayar(): string
    {
        $prefix = 'PAY-' . date('Ymd') . '-';

        $last = static::withTrashed()
            ->where('kode_bayar', 'like', $prefix . '%')
            ->orderByDesc('kode_bayar')
            ->lockForUpdate() // Cegah race condition pada concurrent request
            ->first();

        $num = $last
            ? ((int) substr($last->kode_bayar, strlen($prefix))) + 1
            : 1;

        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}