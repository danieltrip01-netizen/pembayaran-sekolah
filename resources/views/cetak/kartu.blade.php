{{-- resources/views/cetak/kartu.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kartu Uang Sekolah</title>
    <style>
        @page {
            size: 215mm 330mm; /* F4 */
            margin: 5mm;
        }

        * {
            font-family: 'Arial Narrow', Arial, sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body { background: white; }

        .page-wrapper {
            width: 215mm;
            padding: 5mm;
            overflow: hidden;
        }

        .page-break { page-break-after: always; }
        .grid-container { display: block; width: 100%; }

        /* ── KARTU ── */
        .kartu {
            width: 98mm;
            height: 153mm;
            border: 1.5px solid #000;
            padding: 8px;
            float: left;
            margin: 0 0.5mm 1mm 0;
            position: relative;
            background: #fff;
            overflow: hidden;
        }

        .clear-fix { clear: both; }

        /* ── KOP ── */
        .kop {
            display: table;
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .kop-logo-cell {
            display: table-cell;
            width: 45px;
            vertical-align: middle;
        }

        .kop-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .kop-teks-cell {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
        }

        .kop-teks .yayasan      { font-size: 19px; font-weight: bold; }
        .kop-teks .nama-sekolah { font-size: 17px; font-weight: bold; }
        .kop-teks .alamat       { font-size: 11.5px; }

        /* ── JUDUL & INFO ── */
        .judul-kartu { text-align: center; margin-bottom: 6px; }
        .judul-kartu .judul {
            font-size: 15px; font-weight: bold;
            text-decoration: underline; text-transform: uppercase;
        }
        .judul-kartu .tahun { font-size: 10.5px; font-weight: bold; }

        .info-siswa { font-size: 12.5px; margin-bottom: 6px; }
        .info-siswa table { width: 100%; border-collapse: collapse; }
        .info-siswa td    { padding: 1px 0; }
        .info-siswa .lbl  { width: 100px; }
        .info-siswa .sep  { width: 8px; }

        /* ── TABEL + WATERMARK ── */
        .tabel-container { position: relative; }

        .watermark {
            position: absolute;
            top: 50%; left: 50%;
            margin-left: -120px; margin-top: -250px;
            width: 250px;
            opacity: 0.08;
            z-index: 0;
        }

        .tabel-bulan {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            position: relative;
            z-index: 1;
        }

        .tabel-bulan th,
        .tabel-bulan td {
            border: 1px solid #000;
            padding: 2px 2px;
            text-align: center;
        }

        .tabel-bulan th { background: #f0f0f0; font-size: 12px; }
        .tabel-bulan .td-bulan {
            text-align: left; font-weight: bold; padding-left: 3px;
        }

        /* ── FOOTER: QR kiri + TTD kanan ── */
        .footer-container {
            display: table;
            width: 100%;
            margin-top: 6px;
        }

        .qr-cell {
            display: table-cell;
            vertical-align: bottom;
            width: 64px;
        }

        .qr-img {
            width: 56px;
            height: 56px;
            display: block;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        .qr-label {
            font-size: 6.5px;
            color: #666;
            line-height: 1.3;
            text-align: center;
            margin-top: 2px;
            width: 56px;
        }

        .ttd-cell {
            display: table-cell;
            vertical-align: bottom;
            text-align: right;
            padding-right: 10px;
            font-size: 12.5px;
        }

        .ttd-wrapper { position: relative; height: 42px; margin-top: 2px; }

        .ttd-scan {
            position: absolute;
            right: 0; top: -4px;
            width: 55px;
            z-index: 2;
        }

        .nama-kepsek {
            margin-top: 50px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            font-size: 12px;
        }

        .placeholder-kartu {
            border: 1px dashed #eee;
            background: transparent;
        }
    </style>
</head>

<body>

@php
    /*
     * ── Urutkan kartu berdasarkan kelas ──────────────────────────────────────
     * Flatten semua chunk, sort natural (1A < 1B < 2A < TK A), chunk ulang.
     */
    $allItems = collect($chunks)->collapse();

    $sorted = $allItems->sortBy(function ($item) {
        $kelas = $item['kelas_nama'] ?? '';
        preg_match('/^(\d+)(.*)/u', trim($kelas), $m);
        if ($m) {
            return str_pad($m[1], 4, '0', STR_PAD_LEFT) . strtoupper($m[2]);
        }
        return 'ZZZZ' . strtoupper($kelas);
    })->values();

    $chunks = $sorted->chunk(4);
@endphp

@foreach ($chunks as $chunk)
    <div class="page-wrapper {{ !$loop->last ? 'page-break' : '' }}">
        <div class="grid-container">

            @foreach ($chunk as $index => $item)
            @php
                $siswa      = $item['siswa'];
                $tabel      = $item['tabel_bulan'];
                $jenjang    = $siswa->jenjang ?? 'SD';
                $kelasNama  = $item['kelas_nama'];
                $nomSpp     = $item['nominal_spp'];
                $nomMamin   = $item['nominal_mamin'];
                $nomDonatur = $item['nominal_donator'];

                $logoData    = $logoDataMap[$jenjang]    ?? '';
                $ttdData     = $ttdDataMap[$jenjang]     ?? '';
                $kepsek      = $kepsekMap[$jenjang]      ?? 'Kepala Sekolah';
                $namaYayasan = $namaYayasanMap[$jenjang] ?? '';
                $namaSekolah = $namaSekolahMap[$jenjang] ?? $jenjang;
                $alamat      = $alamatMap[$jenjang]      ?? '';

                // QR sudah di-generate di controller sebagai data URI
                $qrSrc = $item['qr_src'] ?? '';
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
                                <td>{{ $kelasNama }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">UANG SEKOLAH</td>
                                <td class="sep">:</td>
                                <td>
                                    Rp {{ number_format($nomSpp, 0, ',', '.') }}
                                    @if($nomMamin > 0)
                                        + Rp {{ number_format($nomMamin, 0, ',', '.') }} (mamin)
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
                                @php $sudahTampilNominal = false; @endphp
                                @foreach ($tabel as $row)
                                <tr>
                                    <td class="td-bulan" style="height:12px">
                                        {{ $row['bulan'] }}
                                    </td>
                                    <td>
                                        @if(!$sudahTampilNominal && $row['aktif'])
                                            {{ number_format($row['uang_sekolah'], 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$sudahTampilNominal && $row['aktif'] && $row['donatur'] > 0)
                                            {{ number_format($row['donatur'], 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$sudahTampilNominal && $row['aktif'])
                                            {{ number_format(
                                                $row['uang_sekolah'] - $row['donatur'] + $row['mamin'],
                                                0, ',', '.'
                                            ) }}
                                            @php $sudahTampilNominal = true; @endphp
                                        @endif
                                    </td>
                                    <td>
                                        @if($row['tanggal_bayar'])
                                            {{ $row['tanggal_bayar'] }}
                                        @endif
                                    </td>
                                    <td></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- FOOTER: QR kiri + TTD kanan --}}
                    <div class="footer-container">

                        <div class="qr-cell">
                            <img src="{{ $qrSrc }}" class="qr-img" alt="QR">
                            <div class="qr-label">Scan untuk<br>riwayat pembayaran</div>
                        </div>

                        <div class="ttd-cell">
                            <div>{{ $kota }}, {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</div>
                            <div>Kepala Sekolah,</div>
                            <div class="ttd-wrapper">
                                @if($ttdData)
                                    <img src="{{ $ttdData }}" class="ttd-scan" alt="ttd">
                                @endif
                                <div class="nama-kepsek">{{ $kepsek }}</div>
                            </div>
                        </div>

                    </div>

                </div>{{-- .kartu --}}

                @if(($index + 1) % 2 == 0)
                    <div class="clear-fix"></div>
                @endif
            @endforeach

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