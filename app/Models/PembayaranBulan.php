<?php

namespace App\Models;

use App\Traits\HasBulanLabel;
use Illuminate\Database\Eloquent\Model;

class PembayaranBulan extends Model
{
    use HasBulanLabel;
    protected $table = 'pembayaran_bulan';

    protected $fillable = [
        'pembayaran_id',
        'siswa_id',
        'bulan',
    ];

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function getBulanLabelAttribute(): string
    {
        return static::formatBulan($this->bulan);
    }

    public function scopeBulan($query, string $bulan)
    {
        return $query->where('bulan', $bulan);
    }
}