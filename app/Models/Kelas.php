<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'nama',
        'jenjang',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function siswaKelas()
    {
        return $this->hasMany(SiswaKelas::class);
    }

    /**
     * Semua siswa yang pernah berada di kelas ini (lintas tahun ajaran).
     *
     * PERBAIKAN:
     * - Tambah whereNull('siswa_kelas.deleted_at') → exclude soft-deleted pivot
     * - Tambah whereNull('siswa.deleted_at')       → exclude siswa yang sudah dihapus
     */
    public function siswa()
    {
        return $this->hasManyThrough(
            Siswa::class,
            SiswaKelas::class,
            'kelas_id',
            'id',
            'id',
            'siswa_id'
        )
        ->whereNull('siswa_kelas.deleted_at')
        ->whereNull('siswa.deleted_at');
    }

    public function scopeJenjang($query, string $jenjang)
    {
        return $query->where('jenjang', $jenjang);
    }

    public function scopeTerurut($query)
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }
}