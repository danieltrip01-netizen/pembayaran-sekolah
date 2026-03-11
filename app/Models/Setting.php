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
        $jenjangList = ['global', 'TK', 'SD', 'SMP'];

        $rows = static::whereIn('jenjang', $jenjangList)
            ->get()
            ->keyBy('jenjang');

        // Batch insert semua yang belum ada (1 query, bukan N query)
        $missing = collect($jenjangList)->reject(fn($k) => isset($rows[$k]));

        if ($missing->isNotEmpty()) {
            $inserts = $missing->map(fn($k) => [
                'jenjang'      => $k,
                'nama_yayasan' => '',
                'nama_sekolah' => $k !== 'global' ? $k . ' Kristen Dorkas' : '',
                'created_at'   => now(),
                'updated_at'   => now(),
            ])->values()->toArray();

            static::insertOrIgnore($inserts);

            // Re-fetch setelah batch insert
            $rows = static::whereIn('jenjang', $jenjangList)
                ->get()
                ->keyBy('jenjang');
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