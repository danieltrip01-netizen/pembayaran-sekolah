{{-- resources/views/laporan/cetak.blade.php --}}
{{--
    Dua mode tampilan:
    • $isYayasan = true  → jenjang kosong  → laporan gabungan semua unit (KOP yayasan, warna navy)
    • $isYayasan = false → jenjang terisi  → laporan satu unit (KOP sekolah, warna per jenjang)
--}}
@php
    /* ── Ambil data instansi dari $settingData (dikirim controller) ──────── */
    $sd          = $settingData;                    // alias pendek
    $namaInstansi = $sd['nama_instansi'];
    $alamat       = $sd['alamat'];
    $telepon      = $sd['telepon'];
    $kota         = $sd['kota'];
    $logoB64      = $sd['logo_b64'];                // null jika tidak ada

    /* TTD */
    $ttdKiri      = $sd['ttd_kiri_jabatan'];
    $ttdKiriNama  = $sd['ttd_kiri_nama'];
    $ttdKiriNip   = $sd['ttd_kiri_nip']      ?? '';
    $ttdKiriB64   = $sd['ttd_kiri_b64'];            // null jika tidak ada
    $ttdKanan     = $sd['ttd_kanan_jabatan'];
    $ttdKananNama = $sd['ttd_kanan_nama'];
    $ttdKananB64  = $sd['ttd_kanan_b64'];

    /* ── Mode: yayasan vs per-jenjang ──────────────────────────────────────── */
    $jenjangFilter = $filter['jenjang'] ?? '';
    $isYayasan     = empty($jenjangFilter);

    /* Palet warna per jenjang */
    $palette = [
        'TK'  => ['primary'=>'#be185d','light'=>'#fdf2f8','border'=>'#f9a8d4','header_bg'=>'#831843'],
        'SD'  => ['primary'=>'#1d4ed8','light'=>'#eff6ff','border'=>'#93c5fd','header_bg'=>'#1e3a8a'],
        'SMP' => ['primary'=>'#065f46','light'=>'#ecfdf5','border'=>'#6ee7b7','header_bg'=>'#064e3b'],
    ];
    $pal = $isYayasan
        ? ['primary'=>'#0C1E3E','light'=>'#f1f5f9','border'=>'#cbd5e1','header_bg'=>'#0f2d56']
        : ($palette[$jenjangFilter] ?? $palette['SD']);

    /* Variabel bantu tabel */
    $showMamin        = $pembayaran->contains(fn($p) => $p->nominal_mamin > 0);
    $tahunId          = $filter['tahun_pelajaran_id'] ?? null;
    $jenjangOrder     = ['TK','SD','SMP'];
    $groupedByJenjang = $pembayaran->groupBy(fn($p) => $p->siswa?->jenjang ?? '-');

    /* Helper: nama kelas dari relasi (bukan kolom siswa.kelas) */
    $getKelas = fn($p) =>
        $p->siswa?->siswaKelas->firstWhere('tahun_pelajaran_id', $tahunId)?->kelas?->nama
        ?? $p->siswa?->siswaKelas->first()?->kelas?->nama
        ?? '&mdash;';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembayaran — {{ $tahunLabel ?? 'Semua Tahun' }}</title>
    <style>
        /* ══ RESET ══════════════════════════════════════════════════════════════ */
        * { font-family: Arial, Helvetica, sans-serif; font-size: 10px;
            margin: 0; padding: 0; box-sizing: border-box; }

        /* F4 = 215mm × 330mm, margin 2cm semua sisi (footer butuh ekstra bawah) */
        body { margin: 0; padding: 2cm 2cm 2.8cm 2cm; color: #1e293b; background: #fff; }

        /* ══ CSS VARIABLES (diisi lewat inline style pada elemen .theme) ═══════ */
        /* Warna tema disisipkan sebagai inline style agar DomPDF kompatibel */

        /* ══ KOP ════════════════════════════════════════════════════════════════ */
        .kop { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .kop td { vertical-align: middle; padding: 0; }
        .kop .logo-cell { width: 52px; padding-right: 10px; }
        .logo-box { width: 48px; height: 48px; text-align: center;
                    color: #fff; font-size: 21px; font-weight: bold; padding-top: 10px; }
        .kop .info-cell .nama { font-size: 13px; font-weight: bold;
                                text-transform: uppercase; letter-spacing: .5px; }
        .kop .info-cell .sub  { font-size: 8.5px; color: #64748b; margin-top: 1px; }
        .kop .meta-cell { width: 175px; text-align: right; }
        .kop .meta-cell .doc-num { font-size: 8.5px; font-weight: bold; }
        .kop .meta-cell .doc-lbl { font-size: 7px; color: #94a3b8;
                                   text-transform: uppercase; letter-spacing: .6px; }
        .kop-divider { height: 3px; margin: 8px 0 6px; }

        /* ══ JUDUL ══════════════════════════════════════════════════════════════ */
        .judul { text-align: center; margin: 4px 0 3px; }
        .judul .utama { font-size: 12px; font-weight: bold;
                        text-transform: uppercase; letter-spacing: 2px; }
        .judul .sub   { font-size: 8.5px; color: #475569; margin-top: 2px; }

        /* ══ GARIS BAWAH JUDUL ══════════════════════════════════════════════════ */
        .judul-line { height: 1px; background: #e2e8f0; margin: 5px 0 6px; }

        /* ══ SECTION TITLE ══════════════════════════════════════════════════════ */
        .sec-title { font-size: 8.5px; font-weight: bold; text-transform: uppercase;
                     letter-spacing: 1px; padding-left: 7px;
                     margin: 10px 0 5px; }

        /* ══ REKAP KELAS TABLE ══════════════════════════════════════════════════ */
        .tbl-rekap { width: 100%; border-collapse: collapse; }
        .tbl-rekap th { color: #fff; padding: 4px 6px;
                        font-size: 8px; font-weight: bold; text-align: left; }
        .tbl-rekap th.r { text-align: right; }
        .tbl-rekap td { padding: 3.5px 6px; font-size: 8.5px;
                        border-bottom: .5px solid #e2e8f0; color: #334155; }
        .tbl-rekap td.r { text-align: right; }
        .tbl-rekap tbody tr:nth-child(even) td { background: #f8fafc; }
        .tbl-rekap tfoot td { font-weight: bold; font-size: 8px; padding: 3.5px 6px;
                              background: #f1f5f9; border-top: 1px solid #cbd5e1; }
        .tbl-rekap tfoot td.r { text-align: right; }

        /* ══ REKAP JENJANG (kotak kanan, hanya mode yayasan) ═══════════════════ */
        .tbl-jenjang { width: 100%; border-collapse: collapse; }
        .tbl-jenjang td { padding: 6px 8px; font-size: 8.5px; border: .5px solid #e2e8f0; }
        .tbl-jenjang .j-lbl { font-weight: bold; width: 36px; }
        .tbl-jenjang .j-trx { color: #64748b; width: 65px; }
        .tbl-jenjang .j-tot { text-align: right; font-weight: bold; }
        .jbg-tk  { border-left: 3px solid #ec4899 !important; }
        .jbg-sd  { border-left: 3px solid #3b82f6 !important; }
        .jbg-smp { border-left: 3px solid #10b981 !important; }

        /* ══ DETAIL TABLE ════════════════════════════════════════════════════════ */
        .tbl-detail { width: 100%; border-collapse: collapse; }
        .tbl-detail thead th { color: #fff; padding: 5px 4px;
                               font-size: 8.5px; font-weight: 700;
                               text-align: left; white-space: nowrap; }
        .tbl-detail thead th.c { text-align: center; }
        .tbl-detail thead th.r { text-align: right; }
        .tbl-detail tbody td { padding: 3.5px 4px; border-bottom: .5px solid #e8edf2;
                               font-size: 8.5px; color: #334155; vertical-align: middle; }
        .tbl-detail tbody td.c  { text-align: center; }
        .tbl-detail tbody td.r  { text-align: right; }
        .tbl-detail tbody td.num{ font-family: 'Courier New', monospace; }
        .tbl-detail tbody tr:nth-child(even) td { background: #f8fafc; }

        /* Aksen kiri per jenjang (hanya mode yayasan) */
        .row-tk  td:first-child { border-left: 3px solid #ec4899; }
        .row-sd  td:first-child { border-left: 3px solid #3b82f6; }
        .row-smp td:first-child { border-left: 3px solid #10b981; }

        /* Sub-total jenjang */
        .sub-row td { background: #f1f5f9 !important; font-weight: bold;
                      font-size: 8px; color: #374151;
                      border-top: 1px solid #d1d5db; border-bottom: 1px solid #d1d5db; }
        .sub-row td.r { text-align: right; }

        /* Grand total footer */
        .tbl-detail tfoot td { color: #fff; font-weight: bold; font-size: 9px; padding: 5px 4px; }
        .tbl-detail tfoot td.r { text-align: right; }

        /* ══ BADGE ═══════════════════════════════════════════════════════════════ */
        .bdg { padding: 1px 4px; font-size: 7px; font-weight: bold; }
        .bdg-tk  { background:#fce7f3; color:#be185d; border:1px solid #f9a8d4; }
        .bdg-sd  { background:#dbeafe; color:#1d4ed8; border:1px solid #93c5fd; }
        .bdg-smp { background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; }

        /* ══ TANDA TANGAN ════════════════════════════════════════════════════════ */
        .ttd { width: 100%; border-collapse: collapse; margin-top: 22px; }
        .ttd td { width: 50%; text-align: center; vertical-align: top;
                  padding: 0 20px; font-size: 8.5px; color: #334155; }
        .ttd .role    { font-size: 8.5px; margin-bottom: 2px; }
        .ttd .spacer  { height: 44px; }
        .ttd .garis   { padding-top: 3px; font-size: 9px;
                        font-weight: bold; color: #1e293b; }
        .ttd .jabatan { font-size: 7.5px; color: #64748b; margin-top: 1px; }

        /* ══ PAGE FOOTER (fixed) ═════════════════════════════════════════════════ */
        .pfooter { position: fixed; bottom: 0.5cm; left: 2cm; right: 2cm;
                   border-top: .5px solid #e2e8f0; padding-top: 4px; }
        .pfooter table { width: 100%; border-collapse: collapse; }
        .pfooter td { font-size: 7.5px; color: #94a3b8; }
        .pfooter td.r { text-align: right; }

        /* ══ MISC ════════════════════════════════════════════════════════════════ */
        .dash { color: #d1d5db; }
        .bold { font-weight: bold; }
        .rekap-wrap { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .rekap-wrap > tbody > tr > td { vertical-align: top; padding: 0; }
    </style>
</head>
<body>

{{-- ══════════════════════════════════════════════════════════════════════════
     KOP SURAT
     • Yayasan → logo navy, nama yayasan, semua unit
     • Per jenjang → logo warna jenjang, nama sekolah, sub "di bawah yayasan"
══════════════════════════════════════════════════════════════════════════ --}}
<table class="kop" cellpadding="0" cellspacing="0">
    <tr>
        <td class="logo-cell">
            @if($logoB64)
                {{-- Logo dari storage (base64 untuk DomPDF) --}}
                <img src="{{ $logoB64 }}" style="width:48px;height:48px;object-fit:contain" alt="logo">
            @else
                {{-- Fallback: kotak warna dengan simbol --}}
                <div class="logo-box" style="background:{{ $pal['header_bg'] }}">&#10013;</div>
            @endif
        </td>
        <td class="info-cell">
            <div class="nama" style="color:{{ $pal['primary'] }}">{{ $namaInstansi }}</div>
            <div class="sub">
                {{ $alamat }}
                @if($telepon) &nbsp;|&nbsp; Telp: {{ $telepon }} @endif
            </div>
        </td>
        <td class="meta-cell">
            <div class="doc-lbl">No. Dokumen</div>
            <div class="doc-num" style="color:{{ $pal['primary'] }}">
                LAP/{{ $isYayasan ? 'YYS' : $jenjangFilter }}/{{ date('Y') }}/{{ str_pad($pembayaran->count(), 4, '0', STR_PAD_LEFT) }}
            </div>
            <div class="doc-lbl" style="margin-top:3px">Dicetak</div>
            <div class="doc-num" style="font-size:8px;color:{{ $pal['primary'] }}">{{ now()->isoFormat('D MMM Y, HH:mm') }}</div>
        </td>
    </tr>
</table>

{{-- Garis divider tebal di bawah KOP --}}
@if($isYayasan)
<div class="kop-divider" style="background:{{ $pal['primary'] }};margin-top:8px"></div>
@else
{{-- Laporan per-jenjang: garis dua lapis (tebal + tipis) --}}
<div style="height:3px;background:{{ $pal['primary'] }};margin-top:8px"></div>
<div style="height:1px;background:{{ $pal['border'] }};margin-top:2px;margin-bottom:6px"></div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     JUDUL
══════════════════════════════════════════════════════════════════════════ --}}
<div class="judul">
    <div class="utama" style="color:{{ $pal['primary'] }}">
        Laporan Pembayaran SPP
    </div>
    <div class="sub">
        Tahun Pelajaran: <strong>{{ $tahunLabel ?? 'Semua Tahun' }}</strong>
        @if(!empty($filter['bulan']))
            &nbsp;&bull;&nbsp; Periode Transaksi: <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $filter['bulan'])->isoFormat('MMMM Y') }}</strong>
        @elseif(!empty($filter['tanggal_dari']) || !empty($filter['tanggal_sampai']))
            &nbsp;&bull;&nbsp; Tgl: <strong>{{ $filter['tanggal_dari'] ?: '&mdash;' }}</strong> s/d <strong>{{ $filter['tanggal_sampai'] ?: '&mdash;' }}</strong>
        @endif
        @if(!empty($filter['kelas']))
            &nbsp;&bull;&nbsp; Kelas: <strong>{{ $filter['kelas'] }}</strong>
        @endif
    </div>
</div>
<div class="judul-line"></div>

{{-- ══════════════════════════════════════════════════════════════════════════
     REKAP
     • Yayasan  : kiri = rekap per jenjang (prominent), kanan = rekap per kelas
     • Per-jenjang: satu tabel rekap per kelas saja (tanpa rekap jenjang)
══════════════════════════════════════════════════════════════════════════ --}}
@if(!empty($perKelas) && $perKelas->isNotEmpty())
<div class="sec-title" style="border-left:3px solid {{ $pal['primary'] }};color:{{ $pal['primary'] }}">
    @if($isYayasan) Rekap per Jenjang &amp; Kelas @else Rekap per Kelas @endif
</div>

@if($isYayasan)
{{-- ── MODE YAYASAN: dua kolom (kiri=jenjang besar, kanan=kelas) ──── --}}
<table class="rekap-wrap" cellpadding="0" cellspacing="0">
    <tbody><tr>

        {{-- Kiri: rekap per jenjang (lebih prominent) --}}
        <td style="width:38%;padding-right:8px;vertical-align:top">
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
                    @foreach($jenjangOrder as $j)
                    @if($groupedByJenjang->has($j))
                    @php
                        $gjRows  = $groupedByJenjang[$j];
                        $gjSiswa = $gjRows->pluck('siswa_id')->unique()->count();
                    @endphp
                    <tr>
                        <td>
                            <span class="bdg bdg-{{ strtolower($j) }}">{{ $j }}</span>
                        </td>
                        <td>{{ $gjSiswa }} siswa</td>
                        <td class="r">{{ $gjRows->count() }}</td>
                        <td class="r bold">Rp&nbsp;{{ number_format($gjRows->sum('total_bayar'), 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="bold" style="color:#0C1E3E">Grand Total</td>
                        <td class="r bold" style="color:#0C1E3E">{{ $pembayaran->count() }}</td>
                        <td class="r bold" style="color:#0C1E3E">Rp&nbsp;{{ number_format($rekap['total_semua'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </td>

        {{-- Kanan: rekap per kelas --}}
        <td style="width:62%;vertical-align:top">
            <table class="tbl-rekap" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th style="background:#334155;width:68px">Kelas</th>
                        <th style="background:#334155;width:34px">Jenjang</th>
                        <th style="background:#334155">Siswa</th>
                        <th class="r" style="background:#334155">Total Pembayaran</th>
                        <th class="r" style="background:#334155">Rata-rata/Siswa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($perKelas as $namaKelas => $data)
                    @php
                        $jKelas = $pembayaran->first(fn($p) =>
                            ($p->siswa?->siswaKelas
                                ->firstWhere('tahun_pelajaran_id', $tahunId)
                                ?->kelas?->nama ?? '-') === $namaKelas
                        )?->siswa?->jenjang ?? '';
                    @endphp
                    <tr>
                        <td class="bold">Kelas {{ $namaKelas }}</td>
                        <td>
                            @if($jKelas)
                                <span class="bdg bdg-{{ strtolower($jKelas) }}">{{ $jKelas }}</span>
                            @endif
                        </td>
                        <td>{{ $data['jumlah_siswa'] }} siswa</td>
                        <td class="r">Rp&nbsp;{{ number_format($data['total'], 0, ',', '.') }}</td>
                        <td class="r">
                            @if($data['jumlah_siswa'] > 0)
                                Rp&nbsp;{{ number_format($data['total'] / $data['jumlah_siswa'], 0, ',', '.') }}
                            @else <span class="dash">&mdash;</span> @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">Total</td>
                        <td>{{ $perKelas->sum('jumlah_siswa') }} siswa</td>
                        <td class="r bold" style="color:#0C1E3E">Rp&nbsp;{{ number_format($perKelas->sum('total'), 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </td>
    </tr></tbody>
</table>

@else
{{-- ── MODE PER JENJANG: hanya rekap kelas, warna tema jenjang ──────── --}}
<table class="tbl-rekap" cellpadding="0" cellspacing="0" style="margin-bottom:10px">
    <thead>
        <tr>
            <th style="background:{{ $pal['header_bg'] }};width:90px">Kelas</th>
            <th style="background:{{ $pal['header_bg'] }}">Siswa</th>
            <th class="r" style="background:{{ $pal['header_bg'] }}">Transaksi</th>
            <th class="r" style="background:{{ $pal['header_bg'] }}">Total Pembayaran</th>
            <th class="r" style="background:{{ $pal['header_bg'] }}">Rata-rata / Siswa</th>
        </tr>
    </thead>
    <tbody>
        @foreach($perKelas as $namaKelas => $data)
        <tr>
            <td class="bold" style="border-left:3px solid {{ $pal['primary'] }}">Kelas {{ $namaKelas }}</td>
            <td>{{ $data['jumlah_siswa'] }} siswa</td>
            <td class="r">{{ $pembayaran->filter(fn($p) =>
                    ($p->siswa?->siswaKelas->firstWhere('tahun_pelajaran_id', $tahunId)?->kelas?->nama ?? '-') === $namaKelas
                )->count() }}</td>
            <td class="r">Rp&nbsp;{{ number_format($data['total'], 0, ',', '.') }}</td>
            <td class="r">
                @if($data['jumlah_siswa'] > 0)
                    Rp&nbsp;{{ number_format($data['total'] / $data['jumlah_siswa'], 0, ',', '.') }}
                @else <span class="dash">&mdash;</span> @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td class="bold" style="color:{{ $pal['primary'] }}">Total</td>
            <td class="bold" style="color:{{ $pal['primary'] }}">{{ $perKelas->sum('jumlah_siswa') }} siswa</td>
            <td class="r bold" style="color:{{ $pal['primary'] }}">{{ $pembayaran->count() }}</td>
            <td class="r bold" style="color:{{ $pal['primary'] }}">Rp&nbsp;{{ number_format($perKelas->sum('total'), 0, ',', '.') }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
@endif
@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     DETAIL TRANSAKSI
     • Yayasan    : ada kolom Jenjang + sub-total per jenjang + aksen warna kiri
     • Per-jenjang: tidak ada kolom Jenjang, tidak ada sub-total jenjang
══════════════════════════════════════════════════════════════════════════ --}}
<div class="sec-title" style="border-left:3px solid {{ $pal['primary'] }};color:{{ $pal['primary'] }}">
    Detail Transaksi
</div>

<table class="tbl-detail" cellpadding="0" cellspacing="0">
    <thead>
        <tr style="background:{{ $pal['header_bg'] }}">
            <th class="c" style="width:18px">No</th>
            <th style="width:52px">Tanggal</th>
            <th>Nama Siswa</th>
            <th class="c" style="width:26px">Kls</th>
            @if($isYayasan)
            <th class="c" style="width:34px">Jenjang</th>
            @endif
            <th style="width:120px">Bulan Dibayar</th>
            <th class="r" style="width:64px">SPP</th>
            <th class="r" style="width:60px">Donatur</th>
            @if($showMamin)
            <th class="r" style="width:50px">Mamin</th>
            @endif
            <th class="r" style="width:66px">Total</th>
            <th style="width:56px">Petugas</th>
        </tr>
    </thead>
    <tbody>
        @php $no=0; $gSpp=0; $gDon=0; $gMamin=0; $gTotal=0; @endphp

        @if($isYayasan)
        {{-- ── Yayasan: loop per jenjang dengan sub-total ──────────────── --}}
        @foreach($jenjangOrder as $jenjang)
        @if($groupedByJenjang->has($jenjang))
        @php
            $rows   = $groupedByJenjang[$jenjang];
            $sSpp   = $rows->sum(fn($p) => $p->nominal_per_bulan * $p->jumlah_bulan);
            $sDon   = $rows->sum('nominal_donator');
            $sMamin = $rows->sum('nominal_mamin');
            $sTotal = $rows->sum('total_bayar');
            $gSpp  += $sSpp; $gDon += $sDon; $gMamin += $sMamin; $gTotal += $sTotal;
            $rc     = 'row-' . strtolower($jenjang);
        @endphp

        @foreach($rows as $p)
        @php $no++; $jn = strtolower($p->siswa?->jenjang ?? ''); @endphp
        <tr class="{{ $rc }}">
            <td class="c" style="color:#94a3b8">{{ $no }}</td>
            <td>{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
            <td class="bold" style="color:#0C1E3E">{{ $p->siswa?->nama ?? '&mdash;' }}</td>
            <td class="c">{!! $getKelas($p) !!}</td>
            <td class="c"><span class="bdg bdg-{{ $jn }}">{{ strtoupper($jn) }}</span></td>
            <td>{{ $p->bulan_label }}</td>
            <td class="r num">{{ number_format($p->nominal_per_bulan * $p->jumlah_bulan, 0, ',', '.') }}</td>
            <td class="r num">
                @if($p->nominal_donator > 0) {{ number_format($p->nominal_donator, 0, ',', '.') }}
                @else <span class="dash">&mdash;</span> @endif
            </td>
            @if($showMamin)
            <td class="r num">
                @if($p->nominal_mamin > 0) {{ number_format($p->nominal_mamin, 0, ',', '.') }}
                @else <span class="dash">&mdash;</span> @endif
            </td>
            @endif
            <td class="r num bold" style="color:#0C1E3E">{{ number_format($p->total_bayar, 0, ',', '.') }}</td>
            <td style="color:#64748b;font-size:8px">{{ $p->user?->name ?? '&mdash;' }}</td>
        </tr>
        @endforeach

        <tr class="sub-row">
            <td colspan="6" style="text-align:right;padding-right:6px">
                Sub-total <span class="bdg bdg-{{ strtolower($jenjang) }}">{{ $jenjang }}</span>
                &nbsp;{{ $rows->count() }} transaksi :
            </td>
            <td class="r num">{{ number_format($sSpp, 0, ',', '.') }}</td>
            <td class="r num">{{ number_format($sDon, 0, ',', '.') }}</td>
            @if($showMamin)
            <td class="r num">{{ number_format($sMamin, 0, ',', '.') }}</td>
            @endif
            <td class="r num">{{ number_format($sTotal, 0, ',', '.') }}</td>
            <td></td>
        </tr>
        @endif
        @endforeach

        @else
        {{-- ── Per Jenjang: loop langsung tanpa sub-total jenjang ───────── --}}
        @foreach($pembayaran as $p)
        @php $no++; @endphp
        <tr>
            <td class="c" style="color:#94a3b8">{{ $no }}</td>
            <td>{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
            <td class="bold" style="color:{{ $pal['primary'] }}">{{ $p->siswa?->nama ?? '&mdash;' }}</td>
            <td class="c">{!! $getKelas($p) !!}</td>
            <td>{{ $p->bulan_label }}</td>
            <td class="r num">{{ number_format($p->nominal_per_bulan * $p->jumlah_bulan, 0, ',', '.') }}</td>
            <td class="r num">
                @if($p->nominal_donator > 0) {{ number_format($p->nominal_donator, 0, ',', '.') }}
                @else <span class="dash">&mdash;</span> @endif
            </td>
            @if($showMamin)
            <td class="r num">
                @if($p->nominal_mamin > 0) {{ number_format($p->nominal_mamin, 0, ',', '.') }}
                @else <span class="dash">&mdash;</span> @endif
            </td>
            @endif
            <td class="r num bold" style="color:{{ $pal['primary'] }}">{{ number_format($p->total_bayar, 0, ',', '.') }}</td>
            <td style="color:#64748b;font-size:8px">{{ $p->user?->name ?? '&mdash;' }}</td>
        </tr>
        @php
            $gSpp   += $p->nominal_per_bulan * $p->jumlah_bulan;
            $gDon   += $p->nominal_donator;
            $gMamin += $p->nominal_mamin;
            $gTotal += $p->total_bayar;
        @endphp
        @endforeach
        @endif

    </tbody>
    <tfoot>
        <tr style="background:{{ $pal['header_bg'] }}">
            <td colspan="{{ $isYayasan ? 6 : 5 }}" class="r" style="font-size:8px;letter-spacing:.4px">
                TOTAL KESELURUHAN &mdash; {{ $pembayaran->count() }} transaksi
            </td>
            <td class="r num">{{ number_format($gSpp, 0, ',', '.') }}</td>
            <td class="r num">{{ number_format($gDon, 0, ',', '.') }}</td>
            @if($showMamin)
            <td class="r num">{{ number_format($gMamin, 0, ',', '.') }}</td>
            @endif
            <td class="r num" style="font-size:10px">{{ number_format($gTotal, 0, ',', '.') }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

{{-- ══════════════════════════════════════════════════════════════════════════
     TANDA TANGAN
══════════════════════════════════════════════════════════════════════════ --}}
<table class="ttd" cellpadding="0" cellspacing="0">
    <tr>
        {{-- Kiri: Kepala Sekolah / Ketua Yayasan --}}
        <td>
            <div class="role">Mengetahui,</div>
            <div class="role bold">{{ $ttdKiri }}</div>
            @if($ttdKiriB64)
                {{-- Gambar tanda tangan dari storage --}}
                <div style="height:8px"></div>
                <img src="{{ $ttdKiriB64 }}" style="height:36px;max-width:120px;object-fit:contain" alt="ttd">
                <div style="height:4px"></div>
            @else
                <div class="spacer"></div>
            @endif
            <div class="garis" style="border-top:1px solid {{ $pal['primary'] }};color:{{ $pal['primary'] }}">
                @if($ttdKiriNama)
                    {{ $ttdKiriNama }}
                @else
                    ( _________________________________ )
                @endif
            </div>
            @if($ttdKiriNip)
                <div class="jabatan">NIP. {{ $ttdKiriNip }}</div>
            @endif
        </td>

        {{-- Kanan: Bendahara / Tata Usaha --}}
        <td>
            <div class="role">{{ $kota }}, {{ now()->isoFormat('D MMMM Y') }}</div>
            <div class="role bold">{{ $ttdKanan }}</div>
            @if($ttdKananB64)
                <div style="height:8px"></div>
                <img src="{{ $ttdKananB64 }}" style="height:36px;max-width:120px;object-fit:contain" alt="ttd">
                <div style="height:4px"></div>
            @else
                <div class="spacer" style="height:40px"></div>
            @endif
            <div class="garis" style="border-top:1px solid {{ $pal['primary'] }};color:{{ $pal['primary'] }}">
                @if($ttdKananNama)
                    {{ $ttdKananNama }}
                @else
                    (  )
                @endif
            </div>
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════════════════
     PAGE FOOTER (fixed)
══════════════════════════════════════════════════════════════════════════ --}}
<div class="pfooter">
    <table cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <strong style="color:{{ $pal['primary'] }}">e-Scrido</strong>
                &mdash; {{ $namaInstansi }}
                &nbsp;&bull;&nbsp; TP: {{ $tahunLabel ?? '&mdash;' }}
            </td>
            <td class="r">Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }}</td>
        </tr>
    </table>
</div>

</body>
</html>