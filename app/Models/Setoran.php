<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setoran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'setoran';

    protected $fillable = [
        'kode_setoran',
        'tanggal_setoran',
        'jenjang',
        'tahun_pelajaran_id',   // TAMBAH: ada di migrasi, sebelumnya tidak di fillable
        'total_nominal',
        'total_mamin',
        'total_keseluruhan',
        'user_id',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_setoran'   => 'date',
        'total_nominal'     => 'decimal:2',
        'total_mamin'       => 'decimal:2',
        'total_keseluruhan' => 'decimal:2',
    ];

    // ─── Relasi ──────────────────────────────────────────────────────

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tahun pelajaran yang terkait dengan setoran ini.
     * Berguna untuk filter laporan per tahun ajaran.
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    // ─── Static Helpers ──────────────────────────────────────────────

    public static function generateKodeSetoran(string $jenjang): string
    {
        $prefix = 'SET-' . strtoupper($jenjang) . '-' . date('Ymd') . '-';
        $last = static::withTrashed()
            ->where('kode_setoran', 'like', $prefix . '%')
            ->orderByDesc('kode_setoran')
            ->lockForUpdate() // Cegah race condition pada concurrent request
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->kode_setoran, strrpos($last->kode_setoran, '-') + 1);
            $num = $lastNumber + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}