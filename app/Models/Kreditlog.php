<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KreditLog extends Model
{
    protected $table = 'kredit_log';

    protected $fillable = [
        'siswa_id',
        'user_id',
        'pembayaran_id',
        'tipe',          // 'masuk' | 'keluar'
        'jumlah',
        'saldo_sebelum',
        'saldo_sesudah',
        'keterangan',
    ];

    protected $casts = [
        'jumlah'        => 'integer',
        'saldo_sebelum' => 'integer',
        'saldo_sesudah' => 'integer',
    ];

    // ─── Relasi ──────────────────────────────────────────────────────────────

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeMasuk($query)
    {
        return $query->where('tipe', 'masuk');
    }

    public function scopeKeluar($query)
    {
        return $query->where('tipe', 'keluar');
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    /**
     * Label tipe kredit dalam Bahasa Indonesia.
     */
    public function getTipeLabelAttribute(): string
    {
        return match ($this->tipe) {
            'masuk'  => 'Kredit Masuk',
            'keluar' => 'Kredit Terpakai',
            default  => ucfirst($this->tipe ?? '-'),
        };
    }

    /**
     * Warna badge sesuai tipe.
     */
    public function getTipeBadgeClassAttribute(): string
    {
        return match ($this->tipe) {
            'masuk'  => 'success',
            'keluar' => 'danger',
            default  => 'secondary',
        };
    }
}