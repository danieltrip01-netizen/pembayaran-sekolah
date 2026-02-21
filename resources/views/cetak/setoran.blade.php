{{-- resources/views/cetak/setoran.blade.php --}}
{{--
    Variabel yang diharapkan dari controller setoran:
    - $setoran         : model Setoran (dengan relasi pembayaran, user)
    - $globalSetting   : Setting::global()  → nama_yayasan, alamat, kota, telepon
    - $jenjangSetting  : Setting::forJenjang($setoran->jenjang) → nama_sekolah, nama_kepala_sekolah, nama_admin
    Jika variabel belum dikirim, blade akan fallback ke nilai $setoran->jenjang.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Setoran - {{ $setoran->kode_setoran }}</title>
    <style>
        * { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 0; }
        body { padding: 30px; line-height: 1.4; }

        /* ── Header ────────────────────────────────────────────────── */
        .header { border-bottom: 2px solid #1B4B8A; padding-bottom: 10px; margin-bottom: 20px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-logo-cell { width: 65px; vertical-align: middle; }
        .header-logo { width: 60px; height: 60px; object-fit: contain; }
        .header-teks { vertical-align: middle; padding-left: 10px; }
        .header-teks h1 { font-size: 16px; color: #1B4B8A; text-transform: uppercase; margin-bottom: 2px; }
        .header-teks p  { font-size: 10px; color: #555; margin: 0; }
        .header-teks .judul-doc {
            font-size: 12px; margin-top: 5px; font-weight: bold; text-transform: uppercase;
        }

        /* ── Info & Tabel ───────────────────────────────────────────── */
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 2px 0; vertical-align: top; }
        .label { color: #666; width: 100px; }
        .value { font-weight: bold; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th {
            background: #1B4B8A; color: white;
            padding: 8px; text-align: center;
            border: 1px solid #1B4B8A;
        }
        table.data-table td { padding: 7px; border: 1px solid #e2e8f0; }

        .total-row { background: #e8f0fe !important; font-weight: bold; }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }

        /* ── TTD ───────────────────────────────────────────────────── */
        .footer-ttd { width: 100%; margin-top: 30px; }
        .ttd-box { text-align: center; width: 45%; vertical-align: top; }
        .ttd-img { height: 55px; margin: 5px 0; }
        .spacer  { height: 60px; }
    </style>
</head>
<body>

@php
    // ── Resolusi data dari setting (dengan fallback aman) ────────────
    $gs = $globalSetting  ?? null;  // Setting global
    $js = $jenjangSetting ?? null;  // Setting jenjang

    $namaYayasan    = $gs?->nama_yayasan      ?: 'Yayasan Kristen Dorkas';
    $alamatYayasan  = collect([$gs?->alamat, $gs?->kota])->filter()->join(', ')
                        ?: 'Jl. Untung Suropati No. 23, Lasem';
    $telepon        = $gs?->telepon           ?: '';
    $namaSekolah    = $js?->nama_sekolah      ?: ($setoran->jenjang . ' Kristen Dorkas');
    $namaKepsek     = $js?->nama_kepala_sekolah ?: '( ........................ )';
    $nipKepsek      = $js?->nip_kepala_sekolah  ?: '';
    $namaAdmin      = $js?->nama_admin          ?: ($setoran->user->nama_lengkap ?? $setoran->user->name);

    // Logo jenjang (base64 jika ada, untuk PDF)
    $logoData = '';
    if ($js?->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($js->logo)) {
        $mime     = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($js->logo);
        $logoData = "data:{$mime};base64,"
            . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($js->logo));
    }
@endphp

    {{-- ── Header ──────────────────────────────────────────────────── --}}
    <div class="header">
        <table class="header-table">
            <tr>
                @if($logoData)
                <td class="header-logo-cell">
                    <img src="{{ $logoData }}" class="header-logo" alt="Logo">
                </td>
                @endif
                <td class="header-teks">
                    <h1>{{ $namaYayasan }}</h1>
                    <p>{{ $namaSekolah }}</p>
                    <p>{{ $alamatYayasan }}{{ $telepon ? ' · Telp. ' . $telepon : '' }}</p>
                    <p class="judul-doc">
                        Rekapitulasi Setoran Per Kelas ({{ strtoupper($setoran->jenjang) }})
                    </p>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Info Setoran ──────────────────────────────────────────────── --}}
    <table class="info-table">
        <tr>
            <td class="label">Kode Setoran</td>
            <td class="value">: {{ $setoran->kode_setoran }}</td>
            <td class="label">Jenjang</td>
            <td class="value">: {{ $setoran->jenjang }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal</td>
            <td class="value">: {{ $setoran->tanggal_setoran->isoFormat('D MMMM Y') }}</td>
            <td class="label">Petugas</td>
            <td class="value">: {{ $setoran->user->nama_lengkap ?? $setoran->user->name }}</td>
        </tr>
    </table>

    {{-- ── Tabel Rekap Per Kelas ────────────────────────────────────── --}}
    <table class="data-table">
        <thead>
            <tr>
                <th>Kelas</th>
                <th>Total Nominal SPP</th>
                <th>Total Mamin</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php
                $rekapPerKelas = $setoran->pembayaran->groupBy(function ($item) {
                    return $item->siswa->kelas ?? 'Tanpa Kelas';
                });
            @endphp

            @foreach($rekapPerKelas as $kelas => $items)
            @php
                $subMamin   = $items->sum('nominal_mamin');
                $subNominal = $items->sum(fn($p) => (float)$p->total_bayar - (float)$p->nominal_mamin);
                $subTotal   = $items->sum('total_bayar');
            @endphp
            <tr>
                <td class="text-center"><strong>Kelas {{ $kelas }}</strong></td>
                <td class="text-right">{{ number_format($subNominal, 0, ',', '.') }}</td>
                <td class="text-right">{{ $subMamin > 0 ? number_format($subMamin, 0, ',', '.') : '-' }}</td>
                <td class="text-right"><strong>{{ number_format($subTotal, 0, ',', '.') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td class="text-center">GRAND TOTAL</td>
                <td class="text-right">{{ number_format($setoran->total_nominal, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($setoran->total_mamin, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($setoran->total_keseluruhan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="font-size:9px;color:#777">
        * Rekapitulasi berdasarkan data dari {{ $setoran->pembayaran->count() }} transaksi pembayaran.
    </p>

    {{-- ── Tanda Tangan ─────────────────────────────────────────────── --}}
    <table class="footer-ttd">
        <tr>
            <td class="ttd-box">
                <p>Mengetahui,</p>
                <p>Kepala Sekolah {{ $setoran->jenjang }}</p>
                <div class="spacer"></div>
                <p><strong>( {{ $namaKepsek }} )</strong></p>
                @if($nipKepsek)
                    <p>NIP. {{ $nipKepsek }}</p>
                @endif
            </td>
            <td style="width:10%"></td>
            <td class="ttd-box">
                <p>{{ collect([$gs?->kota])->filter()->first() ?: 'Lasem' }},
                   {{ $setoran->tanggal_setoran->isoFormat('D MMMM Y') }}</p>
                <p>Bendahara,</p>
                <div class="spacer"></div>
                <p><strong>( {{ $namaAdmin }} )</strong></p>
            </td>
        </tr>
    </table>

</body>
</html>