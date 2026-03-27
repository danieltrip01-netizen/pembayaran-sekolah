{{-- resources/views/laporan/cetak.blade.php --}}

@php
    $sd = $settingData;
    $namaInstansi = $sd['nama_instansi'];
    $alamat = $sd['alamat'];
    $telepon = $sd['telepon'];
    $kota = $sd['kota'];
    $logoB64 = $sd['logo_b64'];

    $ttdKiri = $sd['ttd_kiri_jabatan'];
    $ttdKiriNama = $sd['ttd_kiri_nama'];
    $ttdKiriNip = $sd['ttd_kiri_nip'] ?? '';
    $ttdKiriB64 = $sd['ttd_kiri_b64'];
    $ttdKanan = $sd['ttd_kanan_jabatan'];
    $ttdKananNama = $sd['ttd_kanan_nama'];
    $ttdKananB64 = $sd['ttd_kanan_b64'];

    $jenjangFilter = $filter['jenjang'] ?? '';
    $isYayasan = empty($jenjangFilter);
    $pal = ['primary' => '#1d4ed8', 'light' => '#eff6ff', 'border' => '#93c5fd', 'header_bg' => '#1e3a8a'];
    $showMamin = $pembayaran->contains(fn($p) => $p->nominal_mamin > 0);
    $tahunId = $filter['tahun_pelajaran_id'] ?? null;
    $jenjangOrder = ['TK', 'SD', 'SMP'];
    $groupedByJenjang = $pembayaran->groupBy(fn($p) => $p->siswa?->jenjang ?? '-');
    $getKelas = fn($p) => $p->siswa?->siswaKelas->firstWhere('tahun_pelajaran_id', $tahunId)?->kelas?->nama ??
        ($p->siswa?->siswaKelas->first()?->kelas?->nama ?? '&mdash;');

    $noDoc = 'LAP/' . ($isYayasan ? 'YYS' : $jenjangFilter) . '/' . date('Y') . '/' . date('md');
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pembayaran — {{ $tahunLabel ?? 'Semua Tahun' }}</title>
    <style>
        * {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 2cm 2cm 1.8cm 2cm;
            color: #1e293b;
            background: #fff;
            font-size: 11pt;
            line-height: 1.4;
        }

        /* ══ KOP ══════════════════════════════════════════════════════════════ */
        .kop {
            width: 100%;
            border-collapse: collapse;
        }

        .kop td {
            vertical-align: middle;
            padding: 0;
        }

        .kop .logo-cell {
            width: 68px;
            padding-right: 12px;
        }

        .logo-box {
            width: 60px;
            height: 60px;
            text-align: center;
            color: #fff;
            font-size: 24pt;
            font-weight: bold;
            padding-top: 10px;
        }

        .nama-yayasan {
            font-size: 9pt;
            color: #64748b;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .nama-sekolah {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
            line-height: 1.15;
        }

        .alamat {
            font-size: 9pt;
            color: #475569;
            margin-top: 3px;
        }

        .kop-line-thick {
            height: 3px;
            margin-top: 8px;
        }

        .kop-line-thin {
            height: 1px;
            margin-top: 2px;
            margin-bottom: 8px;
        }

        /* ══ JUDUL ════════════════════════════════════════════════════════════ */
        .judul {
            text-align: center;
            margin: 6px 0 4px;
        }

        .judul .utama {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .judul .sub-judul {
            font-size: 10pt;
            color: #475569;
            margin-top: 3px;
        }

        /* ══ META DOKUMEN ═════════════════════════════════════════════════════ */
        .meta-box {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 10px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }

        .meta-box td {
            font-size: 8.5pt;
            padding: 3px 6px;
            color: #475569;
            vertical-align: top;
        }

        .meta-label {
            font-weight: bold;
            color: #334155;
            width: 100px;
            white-space: nowrap;
        }

        .meta-sep {
            width: 8px;
        }

        .meta-val {
            color: #1e293b;
        }

        /* ══ SECTION TITLE ════════════════════════════════════════════════════ */
        .sec-title {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .8px;
            padding-left: 7px;
            margin: 10px 0 5px;
        }

        /* ══ REKAP TABLE ══════════════════════════════════════════════════════ */
        .tbl-rekap {
            width: 100%;
            border-collapse: collapse;
        }

        .tbl-rekap th {
            color: #fff;
            padding: 5px 6px;
            font-size: 8.5pt;
            font-weight: bold;
            text-align: left;
        }

        .tbl-rekap th.r {
            text-align: right;
        }

        .tbl-rekap td {
            padding: 4px 6px;
            font-size: 9pt;
            border-bottom: .5px solid #e2e8f0;
            color: #334155;
        }

        .tbl-rekap td.r {
            text-align: right;
        }

        .tbl-rekap tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .tbl-rekap tfoot td {
            font-weight: bold;
            font-size: 8.5pt;
            padding: 4px 6px;
            background: #f1f5f9;
            border-top: 1px solid #cbd5e1;
        }

        .tbl-rekap tfoot td.r {
            text-align: right;
        }

        /* ══ DETAIL TABLE ═════════════════════════════════════════════════════ */
        .tbl-detail {
            width: 100%;
            border-collapse: collapse;
        }

        .tbl-detail thead th {
            color: #fff;
            padding: 5px 4px;
            font-size: 8.5pt;
            font-weight: 700;
            text-align: left;
            white-space: nowrap;
        }

        .tbl-detail thead th.c {
            text-align: center;
        }

        .tbl-detail thead th.r {
            text-align: right;
        }

        .tbl-detail tbody td {
            padding: 4px;
            border-bottom: .5px solid #e8edf2;
            font-size: 9pt;
            color: #334155;
            vertical-align: middle;
        }

        .tbl-detail tbody td.c {
            text-align: center;
        }

        .tbl-detail tbody td.r {
            text-align: right;
        }

        .tbl-detail tbody td.num {
            font-family: 'Courier New', monospace;
            font-size: 8.5pt;
        }

        .tbl-detail tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .row-tk td:first-child {
            border-left: 3px solid #ec4899;
        }

        .row-sd td:first-child {
            border-left: 3px solid #3b82f6;
        }

        .row-smp td:first-child {
            border-left: 3px solid #10b981;
        }

        .sub-row td {
            background: #f1f5f9 !important;
            font-weight: bold;
            font-size: 8pt;
            color: #374151;
            border-top: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
        }

        .sub-row td.r {
            text-align: right;
        }

        .tbl-detail tfoot td {
            color: #fff;
            font-weight: bold;
            font-size: 9pt;
            padding: 5px 4px;
        }

        .tbl-detail tfoot td.r {
            text-align: right;
        }

        /* ══ BADGE ════════════════════════════════════════════════════════════ */
        .bdg {
            padding: 1px 4px;
            font-size: 7.5pt;
            font-weight: bold;
        }

        .bdg-tk {
            background: #fce7f3;
            color: #be185d;
            border: 1px solid #f9a8d4;
        }

        .bdg-sd {
            background: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #93c5fd;
        }

        .bdg-smp {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        /* ══ TANDA TANGAN ═════════════════════════════════════════════════════ */
        .ttd {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
        }

        .ttd td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
            font-size: 10pt;
            color: #334155;
        }

        .ttd .role {
            font-size: 10pt;
            margin-bottom: 2px;
        }

        .ttd .spacer {
            height: 48px;
        }

        .ttd .garis {
            padding-top: 3px;
            font-size: 10pt;
            font-weight: bold;
            color: #1e293b;
        }

        .ttd .jabatan {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 1px;
        }

        /* ══ PAGE FOOTER ══════════════════════════════════════════════════════ */
        .pfooter {
            position: fixed;
            bottom: 0.5cm;
            left: 2cm;
            right: 2cm;
            border-top: .5px solid #e2e8f0;
            padding-top: 4px;
        }

        .pfooter table {
            width: 100%;
            border-collapse: collapse;
        }

        .pfooter td {
            font-size: 8pt;
            color: #94a3b8;
        }

        .pfooter td.r {
            text-align: right;
        }

        /* ══ MISC ═════════════════════════════════════════════════════════════ */
        .dash {
            color: #d1d5db;
        }

        .bold {
            font-weight: bold;
        }

        .rekap-wrap {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .rekap-wrap>tbody>tr>td {
            vertical-align: top;
            padding: 0;
        }
    </style>
</head>

<body>

    {{-- ══ KOP ══════════════════════════════════════════════════════════════════ --}}
    <table class="kop" cellpadding="0" cellspacing="0">
        <tr>
            <td class="logo-cell">
                @if ($logoB64)
                    <img src="{{ $logoB64 }}" style="width:60px;height:60px;object-fit:contain" alt="logo">
                @else
                    <div class="logo-box" style="background:{{ $pal['header_bg'] }}">&#10013;</div>
                @endif
            </td>
            <td class="info-cell">
                @if (!$isYayasan && !empty($sd['nama_yayasan']))
                    <div class="nama-yayasan">{{ $sd['nama_yayasan'] }}</div>
                @endif
                <div class="nama-sekolah" style="color:{{ $pal['primary'] }}">{{ $namaInstansi }}</div>
                <div class="alamat">
                    {{ $alamat }}@if ($telepon)
                        &nbsp;|&nbsp; Telp: {{ $telepon }}
                    @endif
                </div>
            </td>
        </tr>
    </table>
    <div class="kop-line-thick" style="background:{{ $pal['primary'] }}"></div>
    <div class="kop-line-thin" style="background:{{ $pal['border'] }}"></div>

    {{-- ══ JUDUL ═════════════════════════════════════════════════════════════════ --}}
    <div class="judul">
        <div class="utama" style="color:{{ $pal['primary'] }}">Laporan Pembayaran SPP</div>
        <div class="sub-judul">
            Tahun Pelajaran: <strong>{{ $tahunLabel ?? 'Semua Tahun' }}</strong>
        </div>
    </div>

    {{-- ══ META DOKUMEN ════════════════════════════════════════════════════════ --}}
    <table class="meta-box" cellpadding="0" cellspacing="0">
        <tr>
            {{-- Kiri: No. Dokumen & Tanggal Cetak --}}
            <td style="width:50%">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="meta-label">No. Dokumen</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val" style="font-weight:bold;color:{{ $pal['primary'] }}">{{ $noDoc }}
                        </td>
                    </tr>
                    <tr>
                        <td class="meta-label">Periode</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">
                            {{ !empty($filter['bulan'])
                                ? \Carbon\Carbon::createFromFormat('Y-m', $filter['bulan'])->isoFormat('MMMM Y')
                                : 'Semua Bulan' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ══ REKAP PER KELAS ══════════════════════════════════════════════════════ --}}
    @if (!empty($perKelas) && $perKelas->isNotEmpty())
        <div class="sec-title" style="border-left:3px solid {{ $pal['primary'] }};color:{{ $pal['primary'] }}">
            @if ($isYayasan)
                Rekap per Jenjang &amp; Kelas
            @else
                Rekap per Kelas
            @endif
        </div>

        @if ($isYayasan)
            <table class="rekap-wrap" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        {{-- Kiri: per jenjang --}}
                        <td style="width:38%;padding-right:8px">
                            <table class="tbl-rekap" cellpadding="0" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th style="background:#0f2d56">Jenjang</th>
                                        <th style="background:#0f2d56">Siswa</th>
                                        <th class="r" style="background:#0f2d56">Transaksi</th>
                                        <th class="r" style="background:#0f2d56">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($jenjangOrder as $j)
                                        @if ($groupedByJenjang->has($j))
                                            @php
                                                $gjRows = $groupedByJenjang[$j];
                                                $gjSiswa = $gjRows->pluck('siswa_id')->unique()->count();
                                            @endphp
                                            <tr>
                                                <td><span
                                                        class="bdg bdg-{{ strtolower($j) }}">{{ $j }}</span>
                                                </td>
                                                <td>{{ $gjSiswa }} siswa</td>
                                                <td class="r">{{ $gjRows->count() }}</td>
                                                <td class="r bold">
                                                    Rp&nbsp;{{ number_format($gjRows->sum('total_bayar'), 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="bold">Grand Total</td>
                                        <td class="r bold">{{ $pembayaran->count() }}</td>
                                        <td class="r bold">
                                            Rp&nbsp;{{ number_format($rekap['total_semua'], 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </td>
                        {{-- Kanan: per kelas --}}
                        <td style="width:62%">
                            <table class="tbl-rekap" cellpadding="0" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th style="background:#334155">Kelas</th>
                                        <th style="background:#334155;width:34px">Jenjang</th>
                                        <th style="background:#334155">Siswa</th>
                                        <th class="r" style="background:#334155">Total Pembayaran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($perKelas as $namaKelas => $data)
                                        @php
                                            $jKelas =
                                                $pembayaran->first(
                                                    fn($p) => ($p->siswa?->siswaKelas->firstWhere(
                                                        'tahun_pelajaran_id',
                                                        $tahunId,
                                                    )?->kelas?->nama ??
                                                        '-') ===
                                                        $namaKelas,
                                                )?->siswa?->jenjang ?? '';
                                        @endphp
                                        <tr>
                                            <td class="bold">Kelas {{ $namaKelas }}</td>
                                            <td>
                                                @if ($jKelas)
                                                    <span
                                                        class="bdg bdg-{{ strtolower($jKelas) }}">{{ $jKelas }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $data['jumlah_siswa'] }} siswa</td>
                                            <td class="r">Rp&nbsp;{{ number_format($data['total'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2">Total</td>
                                        <td>{{ $perKelas->sum('jumlah_siswa') }} siswa</td>
                                        <td class="r bold">
                                            Rp&nbsp;{{ number_format($perKelas->sum('total'), 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        @else
            <table class="tbl-rekap" cellpadding="0" cellspacing="0" style="margin-bottom:10px">
                <thead>
                    <tr>
                        <th style="background:{{ $pal['header_bg'] }};width:90px">Kelas</th>
                        <th style="background:{{ $pal['header_bg'] }}">Siswa</th>
                        <th class="r" style="background:{{ $pal['header_bg'] }}">Transaksi</th>
                        <th class="r" style="background:{{ $pal['header_bg'] }}">Total Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($perKelas as $namaKelas => $data)
                        <tr>
                            <td class="bold" style="border-left:3px solid {{ $pal['primary'] }}">Kelas
                                {{ $namaKelas }}</td>
                            <td>{{ $data['jumlah_siswa'] }} siswa</td>
                            <td class="r">
                                {{ $pembayaran->filter(
                                        fn($p) => ($p->siswa?->siswaKelas->firstWhere('tahun_pelajaran_id', $tahunId)?->kelas?->nama ?? '-') ===
                                            $namaKelas,
                                    )->count() }}
                            </td>
                            <td class="r">Rp&nbsp;{{ number_format($data['total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td class="bold" style="color:{{ $pal['primary'] }}">Total</td>
                        <td class="bold" style="color:{{ $pal['primary'] }}">{{ $perKelas->sum('jumlah_siswa') }}
                            siswa</td>
                        <td class="r bold" style="color:{{ $pal['primary'] }}">{{ $pembayaran->count() }}</td>
                        <td class="r bold" style="color:{{ $pal['primary'] }}">
                            Rp&nbsp;{{ number_format($perKelas->sum('total'), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    @endif

    {{-- ══ DETAIL TRANSAKSI ═════════════════════════════════════════════════════ --}}
    <div class="sec-title" style="border-left:3px solid {{ $pal['primary'] }};color:{{ $pal['primary'] }}">
        Detail Transaksi
    </div>

    <table class="tbl-detail" cellpadding="0" cellspacing="0">
        <thead>
            <tr style="background:{{ $pal['header_bg'] }}">
                <th class="c" style="width:20px">No</th>
                <th style="width:55px">Tanggal</th>
                <th>Nama Siswa</th>
                <th class="c" style="width:28px">Kls</th>
                @if ($isYayasan)
                    <th class="c" style="width:36px">Jenjang</th>
                @endif
                <th style="width:125px">Bulan Dibayar</th>
                <th class="r" style="width:68px">SPP</th>
                <th class="r" style="width:64px">Donatur</th>
                @if ($showMamin)
                    <th class="r" style="width:54px">Mamin</th>
                @endif
                <th class="r" style="width:72px">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 0;
                $gSpp = 0;
                $gDon = 0;
                $gMamin = 0;
                $gTotal = 0;
            @endphp

            @if ($isYayasan)
                @foreach ($jenjangOrder as $jenjang)
                    @if ($groupedByJenjang->has($jenjang))
                        @php
                            $rows = $groupedByJenjang[$jenjang];
                            $sSpp = $rows->sum(fn($p) => $p->nominal_per_bulan * $p->jumlah_bulan);
                            $sDon = $rows->sum('nominal_donator');
                            $sMamin = $rows->sum('nominal_mamin');
                            $sTotal = $rows->sum('total_bayar');
                            $gSpp += $sSpp;
                            $gDon += $sDon;
                            $gMamin += $sMamin;
                            $gTotal += $sTotal;
                            $rc = 'row-' . strtolower($jenjang);
                            // colspan: No+Tgl+Nama+Kls+Jenjang+Bulan = 6
                            $subColspan = $showMamin ? 6 : 6;
                        @endphp
                        @foreach ($rows as $p)
                            @php
                                $no++;
                                $jn = strtolower($p->siswa?->jenjang ?? '');
                            @endphp
                            <tr class="{{ $rc }}">
                                <td class="c" style="color:#94a3b8">{{ $no }}</td>
                                <td>{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
                                <td class="bold" style="color:#0C1E3E">{{ $p->siswa?->nama ?? '&mdash;' }}</td>
                                <td class="c">{!! $getKelas($p) !!}</td>
                                <td class="c"><span
                                        class="bdg bdg-{{ $jn }}">{{ strtoupper($jn) }}</span></td>
                                <td>{{ $p->bulan_label }}</td>
                                <td class="r num">
                                    {{ number_format($p->nominal_per_bulan * $p->jumlah_bulan, 0, ',', '.') }}</td>
                                <td class="r num">
                                    @if ($p->nominal_donator > 0)
                                        {{ number_format($p->nominal_donator, 0, ',', '.') }}
                                    @else
                                        <span class="dash">&mdash;</span>
                                    @endif
                                </td>
                                @if ($showMamin)
                                    <td class="r num">
                                        @if ($p->nominal_mamin > 0)
                                            {{ number_format($p->nominal_mamin, 0, ',', '.') }}
                                        @else
                                            <span class="dash">&mdash;</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="r num bold" style="color:#0C1E3E">
                                    {{ number_format($p->total_bayar, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr class="sub-row">
                            <td colspan="6" style="text-align:right;padding-right:6px">
                                Sub-total <span class="bdg bdg-{{ strtolower($jenjang) }}">{{ $jenjang }}</span>
                                &nbsp;{{ $rows->count() }} transaksi :
                            </td>
                            <td class="r num">{{ number_format($sSpp, 0, ',', '.') }}</td>
                            <td class="r num">{{ number_format($sDon, 0, ',', '.') }}</td>
                            @if ($showMamin)
                                <td class="r num">{{ number_format($sMamin, 0, ',', '.') }}</td>
                            @endif
                            <td class="r num">{{ number_format($sTotal, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                @endforeach
            @else
                @foreach ($pembayaran as $p)
                    @php $no++; @endphp
                    <tr>
                        <td class="c" style="color:#94a3b8">{{ $no }}</td>
                        <td>{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
                        <td class="bold" style="color:{{ $pal['primary'] }}">{{ $p->siswa?->nama ?? '&mdash;' }}
                        </td>
                        <td class="c">{!! $getKelas($p) !!}</td>
                        <td>{{ $p->bulan_label }}</td>
                        <td class="r num">{{ number_format($p->nominal_per_bulan * $p->jumlah_bulan, 0, ',', '.') }}
                        </td>
                        <td class="r num">
                            @if ($p->nominal_donator > 0)
                                {{ number_format($p->nominal_donator, 0, ',', '.') }}
                            @else
                                <span class="dash">&mdash;</span>
                            @endif
                        </td>
                        @if ($showMamin)
                            <td class="r num">
                                @if ($p->nominal_mamin > 0)
                                    {{ number_format($p->nominal_mamin, 0, ',', '.') }}
                                @else
                                    <span class="dash">&mdash;</span>
                                @endif
                            </td>
                        @endif
                        <td class="r num bold" style="color:{{ $pal['primary'] }}">
                            {{ number_format($p->total_bayar, 0, ',', '.') }}</td>
                    </tr>
                    @php
                        $gSpp += $p->nominal_per_bulan * $p->jumlah_bulan;
                        $gDon += $p->nominal_donator;
                        $gMamin += $p->nominal_mamin;
                        $gTotal += $p->total_bayar;
                    @endphp
                @endforeach
            @endif
        </tbody>
        <tfoot>
            <tr style="background:{{ $pal['header_bg'] }}">
                {{-- Per jenjang: No+Tgl+Nama+Kls+Bulan = 5 kolom
                 Yayasan   : No+Tgl+Nama+Kls+Jenjang+Bulan = 6 kolom --}}
                <td colspan="{{ $isYayasan ? 6 : 5 }}" class="r" style="letter-spacing:.4px">
                    TOTAL KESELURUHAN &mdash; {{ $pembayaran->count() }} transaksi
                </td>
                <td class="r num">{{ number_format($gSpp, 0, ',', '.') }}</td>
                <td class="r num">{{ number_format($gDon, 0, ',', '.') }}</td>
                @if ($showMamin)
                    <td class="r num">{{ number_format($gMamin, 0, ',', '.') }}</td>
                @endif
                <td class="r num" style="font-size:10pt">{{ number_format($gTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ══ TANDA TANGAN ═════════════════════════════════════════════════════════ --}}
    <table class="ttd" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="role">Mengetahui,</div>
                <div class="role bold">{{ $ttdKiri }}</div>
                @if ($ttdKiriB64)
                    <div style="height:8px"></div>
                    <img src="{{ $ttdKiriB64 }}" style="height:40px;max-width:120px;object-fit:contain"
                        alt="ttd">
                    <div style="height:4px"></div>
                @else
                    <div class="spacer"></div>
                @endif
                <div class="garis" style="border-top:1px solid {{ $pal['primary'] }};color:{{ $pal['primary'] }}">
                    {{ $ttdKiriNama ?: '( _________________________________ )' }}
                </div>
                @if ($ttdKiriNip)
                    <div class="jabatan">NIP. {{ $ttdKiriNip }}</div>
                @endif
            </td>
            <td>
                <div class="role">{{ $kota }}, {{ now()->isoFormat('D MMMM Y') }}</div>
                <div class="role bold">{{ $ttdKanan }}</div>
                @if ($ttdKananB64)
                    <div style="height:8px"></div>
                    <img src="{{ $ttdKananB64 }}" style="height:40px;max-width:120px;object-fit:contain"
                        alt="ttd">
                    <div style="height:4px"></div>
                @else
                    <div class="spacer"></div>
                @endif
                <div class="garis" style="border-top:1px solid {{ $pal['primary'] }};color:{{ $pal['primary'] }}">
                    {{ $ttdKananNama ?: '( _________________________________ )' }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ══ PAGE FOOTER ══════════════════════════════════════════════════════════ --}}
    <div class="pfooter">
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <strong style="color:{{ $pal['primary'] }}">DPay</strong>
                    &mdash; {{ $namaInstansi }}
                    &nbsp;&bull;&nbsp; TP: {{ $tahunLabel ?? '&mdash;' }}
                </td>
                <td class="r">Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }}</td>
            </tr>
        </table>
    </div>

</body>

</html>
