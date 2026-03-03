<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pembayaran';

    protected $fillable = [
        'kode_bayar',
        'siswa_id',
        'user_id',
        'setoran_id',
        'bulan_dari',
        'bulan_sampai',
        'tahun',
        'bulan_bayar',          // JSON array ['2024-07', '2024-08', ...]
        'nominal_per_bulan',
        'nominal_donator',
        'nominal_mamin',
        'kredit_digunakan',
        'jumlah_bulan',
        'total_bayar',
        'tanggal_bayar',
        'status',               // 'lunas' / 'belum'
        'keterangan',
    ];

    protected $casts = [
        'tanggal_bayar'     => 'date',
        'bulan_bayar'       => 'array',
        'nominal_per_bulan' => 'decimal:2',
        'nominal_mamin'     => 'decimal:2',
        'nominal_donator'   => 'decimal:2',
        'total_bayar'       => 'decimal:2',
        'kredit_digunakan'  => 'integer',
        'jumlah_bulan'      => 'integer',
        'bulan_dari'        => 'integer',
        'bulan_sampai'      => 'integer',
        'tahun'             => 'integer',
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

    public function kreditLog()
    {
        return $this->hasMany(KreditLog::class);
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    /**
     * Label bulan yang terbaca manusia: "Jul 2024, Agu 2024" dst.
     * Prioritas: gunakan bulan_bayar (array JSON), fallback ke bulan_dari/sampai/tahun.
     */
    public function getBulanLabelAttribute(): string
    {
        $namaBulan = [
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'Mei',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Agu',
            '09' => 'Sep',
            '10' => 'Okt',
            '11' => 'Nov',
            '12' => 'Des',
        ];

        $bulanArr = $this->bulan_bayar;

        if (!empty($bulanArr) && is_array($bulanArr)) {
            $labels = array_map(function ($b) use ($namaBulan) {
                if (!str_contains($b, '-')) return $b;
                [$tahun, $bulan] = explode('-', $b, 2);
                return ($namaBulan[$bulan] ?? $bulan) . ' ' . $tahun;
            }, $bulanArr);

            return implode(', ', $labels);
        }

        // Fallback: gunakan bulan_dari/bulan_sampai/tahun
        if ($this->bulan_dari && $this->tahun) {
            $dari   = str_pad($this->bulan_dari, 2, '0', STR_PAD_LEFT);
            $sampai = str_pad($this->bulan_sampai ?? $this->bulan_dari, 2, '0', STR_PAD_LEFT);
            $tahun  = $this->tahun;

            if ($dari === $sampai) {
                return ($namaBulan[$dari] ?? $dari) . ' ' . $tahun;
            }

            return ($namaBulan[$dari] ?? $dari) . '–' . ($namaBulan[$sampai] ?? $sampai) . ' ' . $tahun;
        }

        return '-';
    }

    /**
     * Tagihan bruto sebelum potongan kredit.
     */
    public function getTagiBrutoAttribute(): int
    {
        return (int) $this->total_bayar + (int) ($this->kredit_digunakan ?? 0);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /**
     * Filter pembayaran belum disetor.
     */
    public function scopeBelumSetor($query)
    {
        return $query->whereNull('setoran_id');
    }

    /**
     * Filter pembayaran sudah disetor.
     */
    public function scopeSudahSetor($query)
    {
        return $query->whereNotNull('setoran_id');
    }

    // ─── Static Helpers ──────────────────────────────────────────────────────

    /**
     * Generate kode pembayaran unik per hari: PAY-20240715-0001
     * Menggunakan withTrashed() agar nomor tidak bentrok dengan data yang dihapus.
     */
    public static function generateKodeBayar(): string
    {
        $prefix = 'PAY-' . date('Ymd') . '-';

        $last = static::withTrashed()
            ->where('kode_bayar', 'like', $prefix . '%')
            ->orderBy('kode_bayar', 'desc')
            ->lockForUpdate()
            ->first();

        $num = $last ? ((int) substr($last->kode_bayar, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
