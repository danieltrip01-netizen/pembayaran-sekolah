<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pembayaran';

    protected $fillable = [
        'kode_bayar',
        'siswa_id',
        'setoran_id',
        'user_id',
        'tanggal_bayar',
        'bulan_bayar',
        'jumlah_bulan',
        'nominal_per_bulan',
        'nominal_mamin',
        'nominal_donator',
        'total_bayar',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_bayar'    => 'date',
        'bulan_bayar'      => 'array',
        'nominal_per_bulan' => 'decimal:2',
        'nominal_mamin'    => 'decimal:2',
        'nominal_donator'  => 'decimal:2',
        'total_bayar'      => 'decimal:2',
    ];

    // Relasi
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

    // Accessor
    public function getBulanLabelAttribute(): string
    {
        if (empty($this->bulan_bayar)) return '-';

        $namaBulan = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar',
            '04' => 'Apr', '05' => 'Mei', '06' => 'Jun',
            '07' => 'Jul', '08' => 'Agu', '09' => 'Sep',
            '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
        ];

        $labels = array_map(function ($b) use ($namaBulan) {
            [$tahun, $bulan] = explode('-', $b);
            return ($namaBulan[$bulan] ?? $bulan) . ' ' . $tahun;
        }, $this->bulan_bayar);

        return implode(', ', $labels);
    }

    // Generate kode bayar
    public static function generateKodeBayar(): string
    {
        $prefix = 'PAY-' . date('Ymd') . '-';
        $last = static::where('kode_bayar', 'like', $prefix . '%')
                      ->orderBy('kode_bayar', 'desc')
                      ->first();

        $num = $last ? ((int) substr($last->kode_bayar, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}