<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KreditLog extends Model
{
    use SoftDeletes;

    protected $table = 'kredit_log';

    protected $fillable = [
        'siswa_id',
        'user_id',
        'pembayaran_id',
        'tipe',
        'jumlah',
        'saldo_sebelum',
        'saldo_sesudah',
        'keterangan',
        'dibatalkan_oleh_id',
    ];

    protected $casts = [
        'jumlah'        => 'decimal:2',
        'saldo_sebelum' => 'decimal:2',
        'saldo_sesudah' => 'decimal:2',
    ];

    // ─── Relasi ──────────────────────────────────────────────────────

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

    public function dibatalkanOleh()
    {
        return $this->belongsTo(User::class, 'dibatalkan_oleh_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    /**
     * Kredit yang ditambahkan (kelebihan bayar, manual top-up, dll).
     * Sebelumnya: scopeMasuk → diganti scopeTambah sesuai enum migrasi.
     */
    public function scopeTambah($query)
    {
        return $query->where('tipe', 'tambah');
    }

    /**
     * Kredit yang dipakai untuk potongan pembayaran.
     * Sebelumnya: scopeKeluar → diganti scopePakai sesuai enum migrasi.
     */
    public function scopePakai($query)
    {
        return $query->where('tipe', 'pakai');
    }

    // ─── Accessors ───────────────────────────────────────────────────

    /**
     * Label tipe kredit dalam Bahasa Indonesia.
     */
    public function getTipeLabelAttribute(): string
    {
        return match ($this->tipe) {
            'tambah' => 'Kredit Masuk',    // 'tambah' di DB → label tetap ramah
            'pakai'  => 'Kredit Terpakai',
            default  => ucfirst($this->tipe ?? '-'),
        };
    }

    /**
     * Warna badge sesuai tipe.
     */
    public function getTipeBadgeClassAttribute(): string
    {
        return match ($this->tipe) {
            'tambah' => 'success',
            'pakai'  => 'danger',
            default  => 'secondary',
        };
    }
}