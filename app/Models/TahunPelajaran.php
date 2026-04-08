<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TahunPelajaran extends Model
{
    protected $table = 'tahun_pelajaran';

    protected $fillable = [
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'is_locked',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'is_active'       => 'boolean',
        'is_locked'       => 'boolean',
    ];

    // ─── Boot ────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        // Clear cache setiap kali data tahun pelajaran berubah
        $clearCache = function () {
            Cache::forget('tahun_pelajaran_aktif');
        };

        static::saved($clearCache);
        static::deleted($clearCache);

        $deactivateOthers = function (self $model) {
            if ($model->is_active && $model->isDirty('is_active')) {
                static::query()
                    ->when(
                        $model->exists,
                        fn($q) => $q->where('id', '!=', $model->id)
                    )
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        };

        static::creating($deactivateOthers);
        static::updating($deactivateOthers);
    }

    // ─── Relasi ──────────────────────────────────────────────────────

    public function siswaKelas()
    {
        return $this->hasMany(SiswaKelas::class);
    }

    public function setoran()
    {
        return $this->hasMany(Setoran::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTidakDikunci($query)
    {
        return $query->where('is_locked', false);
    }

    // ─── Static Helpers ──────────────────────────────────────────────

    public static function aktif(): ?static
    {
        if (Cache::has('tahun_pelajaran_aktif')) {
            return Cache::get('tahun_pelajaran_aktif');
        }

        $tahun = static::where('is_active', true)->first();

        if ($tahun) {
            Cache::put('tahun_pelajaran_aktif', $tahun, now()->addMinutes(30));
        }

        return $tahun;
    }

    // ─── Accessors ───────────────────────────────────────────────────

    /**
     * Ambil tahun ajaran (angka) dari nama: "2025/2026" → 2025
     */
    public function getTahunAjaranAttribute(): int
    {
        return (int) explode('/', $this->nama)[0];
    }
}
