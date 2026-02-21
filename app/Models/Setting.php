<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'jenjang',
        'nama_yayasan',
        'alamat',
        'kota',
        'telepon',
        'logo',
        'nama_sekolah',
        'nama_kepala_sekolah',
        'nip_kepala_sekolah',
        'nama_admin',
        'tanda_tangan',
    ];

    // ── Static helpers ───────────────────────────────────────────────

    /** Ambil setting global (yayasan). */
    public static function global(): static
    {
        return static::firstOrCreate(
            ['jenjang' => 'global'],
            ['nama_yayasan' => '']
        );
    }

    /** Ambil setting untuk jenjang tertentu: TK / SD / SMP. */
    public static function forJenjang(string $jenjang): static
    {
        return static::firstOrCreate(
            ['jenjang' => $jenjang],
            ['nama_sekolah' => $jenjang . ' Kristen Dorkas']
        );
    }

    /**
     * Ambil semua setting dalam array terindeks.
     * Return: ['global' => Setting, 'TK' => Setting, 'SD' => Setting, 'SMP' => Setting]
     */
    public static function allIndexed(): array
    {
        $rows = static::whereIn('jenjang', ['global', 'TK', 'SD', 'SMP'])
            ->get()
            ->keyBy('jenjang');

        foreach (['global', 'TK', 'SD', 'SMP'] as $key) {
            if (!isset($rows[$key])) {
                $rows[$key] = static::forJenjang($key === 'global' ? 'global' : $key);
            }
        }

        return $rows->all();
    }

    // ── Accessors ────────────────────────────────────────────────────

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function getTandaTanganUrlAttribute(): ?string
    {
        return $this->tanda_tangan ? asset('storage/' . $this->tanda_tangan) : null;
    }
}