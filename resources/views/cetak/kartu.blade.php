{{-- resources/views/cetak/kartu.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Uang Sekolah</title>
    <style>
        @page {
            size: 215mm 330mm; /* F4 */
            margin: 0;
        }
        * {
            font-family: 'Arial Narrow', Arial, sans-serif;
            box-sizing: border-box;
            margin: 0; padding: 0;
        }
        body { background: white; }

        /*
         * FIX HALAMAN KOSONG:
         * - Hapus page-break-after dari .page-wrapper
         * - Gunakan class .page-break terpisah yang hanya dipasang
         *   di antara halaman (bukan setelah halaman terakhir)
         *   → dikontrol dari Blade via $loop->last
         */
        .page-wrapper {
            width: 215mm;
            padding: 7mm;
            overflow: hidden;
        }
        .page-break {
            page-break-after: always;
        }

        .grid-container { display: block; width: 100%; }

        /* ── KARTU ──────────────────────────────────────────── */
        .kartu {
            width: 95mm;
            height: 150mm;
            border: 1.5px solid #000;
            padding: 8px;
            float: left;
            margin: 0.5mm;
            position: relative;
            background: #fff;
            overflow: hidden;
        }
        .clear-fix { clear: both; }

        /* ── KOP ────────────────────────────────────────────── */
        .kop {
            display: table; width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 4px; margin-bottom: 6px;
        }
        .kop-logo-cell { display: table-cell; width: 45px; vertical-align: middle; }
        .kop-logo      { width: 50px; height: 50px; object-fit: contain; }
        .kop-teks-cell { display: table-cell; text-align: center; vertical-align: middle; }
        .kop-teks .yayasan      { font-size: 18px; font-weight: bold; }
        .kop-teks .nama-sekolah { font-size: 16px; font-weight: bold; }
        .kop-teks .alamat       { font-size: 11px; }

        /* ── JUDUL & INFO ───────────────────────────────────── */
        .judul-kartu { text-align: center; margin-bottom: 6px; }
        .judul-kartu .judul { font-size: 14px; font-weight: bold; text-decoration: underline; text-transform: uppercase; }
        .judul-kartu .tahun { font-size: 10px; font-weight: bold; }

        .info-siswa { font-size: 12px; margin-bottom: 6px; }
        .info-siswa table { width: 100%; border-collapse: collapse; }
        .info-siswa td    { padding: 1px 0; }
        .info-siswa .lbl  { width: 70px; }
        .info-siswa .sep  { width: 8px; }

        /* ── TABEL + WATERMARK ──────────────────────────────── */
        .tabel-container { position: relative; }
        .watermark {
            position: absolute;
            top: 50%; left: 50%;
            margin-left: -100px; margin-top: -230px;
            width: 200px; opacity: 0.08; z-index: 0;
            filter: grayscale(100%);
        }
        .tabel-bulan {
            width: 100%; border-collapse: collapse;
            font-size: 12px; position: relative; z-index: 1;
        }
        .tabel-bulan th,
        .tabel-bulan td { border: 1px solid #000; padding: 1.5px 2px; text-align: center; }
        .tabel-bulan th  { background: #f0f0f0; font-size: 12px; }
        .tabel-bulan .td-bulan { text-align: left; font-weight: bold; padding-left: 3px; }

        /* ── FOOTER / TTD ───────────────────────────────────── */
        .ttd-container { text-align: right; padding-right: 10px; font-size: 12px; margin-top: 20px; }
        .ttd-wrapper   { position: relative; height: 42px; margin-top: 2px; }
        .ttd-scan {
            position: absolute; right: 0; top: -4px;
            width: 55px; z-index: 2;
        }
        .nama-kepsek {
            margin-top: 50px; font-weight: bold;
            text-decoration: underline; text-transform: uppercase;
            font-size: 12px;
        }
        .placeholder-kartu { border: 1px dashed #eee; background: transparent; }
    </style>
</head>
<body>

@foreach ($chunks as $chunk)

{{--
    FIX: class page-break HANYA dipasang di elemen sebelum ganti halaman,
    bukan di halaman terakhir. Ini mencegah halaman kosong di akhir dokumen.
--}}
<div class="page-wrapper {{ !$loop->last ? 'page-break' : '' }}">
    <div class="grid-container">

        @foreach ($chunk as $index => $item)
        @php
            $siswa    = $item['siswa'];
            $tabel    = $item['tabel_bulan'];
            $jenjang  = $siswa->jenjang ?? 'SD';

            $logoData    = $logoDataMap[$jenjang]    ?? '';
            $ttdData     = $ttdDataMap[$jenjang]     ?? '';
            $kepsek      = $kepsekMap[$jenjang]      ?? 'Kepala Sekolah';
            $namaYayasan = $namaYayasanMap[$jenjang] ?? '';
            $namaSekolah = $namaSekolahMap[$jenjang] ?? $jenjang;
            $alamat      = $alamatMap[$jenjang]      ?? '';
        @endphp

        <div class="kartu">

            {{-- KOP --}}
            <div class="kop">
                <div class="kop-logo-cell">
                    @if($logoData)
                        <img src="{{ $logoData }}" class="kop-logo" alt="Logo">
                    @endif
                </div>
                <div class="kop-teks-cell">
                    <div class="kop-teks">
                        <div class="yayasan">{{ strtoupper($namaYayasan) }}</div>
                        <div class="nama-sekolah">{{ strtoupper($namaSekolah) }}</div>
                        <div class="alamat">{{ strtoupper($alamat) }}</div>
                    </div>
                </div>
            </div>

            {{-- JUDUL --}}
            <div class="judul-kartu">
                <div class="judul">Kartu Uang Sekolah</div>
                <div class="tahun">TAHUN PELAJARAN {{ $tahunAjaran }}/{{ $tahunAjaran + 1 }}</div>
            </div>

            {{-- INFO SISWA --}}
            <div class="info-siswa">
                <table>
                    <tr>
                        <td class="lbl">NAMA</td>
                        <td class="sep">:</td>
                        <td><strong>{{ strtoupper($siswa->nama) }}</strong></td>
                    </tr>
                    <tr>
                        <td class="lbl">KELAS</td>
                        <td class="sep">:</td>
                        <td>{{ $siswa->kelas }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">UANG SEKOLAH</td>
                        <td class="sep">:</td>
                        <td>
                            Rp {{ number_format($siswa->nominal_pembayaran, 0, ',', '.') }}
                            @if($siswa->nominal_mamin > 0)
                                + Rp {{ number_format($siswa->nominal_mamin, 0, ',', '.') }} (mamin)
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            {{-- TABEL BULAN --}}
            <div class="tabel-container">
                @if($logoData)
                    <img src="{{ $logoData }}" class="watermark" alt="">
                @endif
                <table class="tabel-bulan">
                    <thead>
                        <tr>
                            <th width="16%" height="20px">Bulan</th>
                            <th width="18%">Uang Sekolah</th>
                            <th width="14%">Donatur</th>
                            <th width="18%">Yang dibayar</th>
                            <th width="18%">Tanggal</th>
                            <th width="16%">Paraf</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tabel as $row)
                        <tr>
                            <td class="td-bulan" style="height:11px">{{ $row['bulan'] }}</td>
                            <td>
                                {{ $loop->first && $row['aktif']
                                    ? number_format($row['uang_sekolah'], 0, ',', '.') : '' }}
                            </td>
                            <td>
                                {{ $loop->first && $row['aktif']
                                    ? number_format($row['donatur'], 0, ',', '.') : '' }}
                            </td>
                            <td>{{ $loop->first && $row['aktif'] ? number_format($row['yang_dibayar'], 0, ',', '.') : '' }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TTD --}}
            <div class="ttd-container">
                <div>{{ $kota }}, {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</div>
                <div style="margin-bottom:3px">Kepala Sekolah,</div>
                <div class="ttd-wrapper">
                    @if($ttdData)
                        <img src="{{ $ttdData }}" class="ttd-scan" alt="ttd">
                    @endif                
                    <div class="nama-kepsek">{{ $kepsek }}</div>
                </div>
            </div>

        </div>{{-- .kartu --}}

        @if(($index + 1) % 2 == 0)
            <div class="clear-fix"></div>
        @endif
        @endforeach

        {{-- Isi slot kosong agar layout tetap terjaga --}}
        @php $count = $chunk->count(); @endphp
        @for ($i = $count; $i < 4; $i++)
            <div class="kartu placeholder-kartu"></div>
            @if(($i + 1) % 2 == 0)
                <div class="clear-fix"></div>
            @endif
        @endfor

    </div>
</div>{{-- .page-wrapper --}}

@endforeach

</body>
</html>