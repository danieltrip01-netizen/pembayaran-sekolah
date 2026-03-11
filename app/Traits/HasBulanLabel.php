<?php

namespace App\Traits;

trait HasBulanLabel
{
    protected static array $namaBulan = [
        '01' => 'Jan', '02' => 'Feb', '03' => 'Mar',
        '04' => 'Apr', '05' => 'Mei', '06' => 'Jun',
        '07' => 'Jul', '08' => 'Agu', '09' => 'Sep',
        '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
    ];

    public static function formatBulan(string $bulan): string
    {
        if (!str_contains($bulan, '-')) return $bulan;

        [$tahun, $bln] = explode('-', $bulan, 2);

        return (static::$namaBulan[$bln] ?? $bln) . ' ' . $tahun;
    }

    public static function formatBulanList(array $bulanList, int $maxTampil = 3): string
    {
        $labels = array_map([static::class, 'formatBulan'], $bulanList);
        $sisa   = count($labels) - $maxTampil;

        return implode(', ', array_slice($labels, 0, $maxTampil))
            . ($sisa > 0 ? " (+{$sisa} lainnya)" : '');
    }
}
