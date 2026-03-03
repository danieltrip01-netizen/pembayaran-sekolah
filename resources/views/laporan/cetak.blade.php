{{-- resources/views/laporan/cetak.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembayaran</title>
    <style>
        * { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 0; box-sizing: border-box; }
        body { padding: 14px; color: #1e293b; }

        /* ── KOP ── */
        .kop {
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 3px double #0C1E3E;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .kop-logo {
            width: 44px; height: 44px;
            background: #0C1E3E;
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 20px;
            flex-shrink: 0;
        }

        .kop-text h1 { font-size: 13px; color: #0C1E3E; margin-bottom: 2px; }
        .kop-text p  { font-size: 9px; color: #64748b; margin: 1px 0; }

        /* ── JUDUL ── */
        .judul-laporan {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
            color: #0C1E3E;
        }

        /* ── FILTER INFO ── */
        .filter-info {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 5px 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 9px;
            color: #64748b;
        }

        /* ── SUMMARY ── */
        .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 10px;
        }

        .summary-box {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
        }

        .summary-box .lbl { color: #64748b; font-size: 8px; margin-bottom: 2px; }
        .summary-box .val { font-weight: bold; color: #0C1E3E; font-size: 10px; }

        .summary-box.grand {
            background: #0C1E3E;
            border-color: #0C1E3E;
        }
        .summary-box.grand .lbl { color: rgba(255,255,255,.6); }
        .summary-box.grand .val { color: #ffffff; }

        /* ── TABLE ── */
        table { width: 100%; border-collapse: collapse; }

        thead th {
            background: #0C1E3E;
            color: white;
            padding: 5px 4px;
            font-size: 9px;
            font-weight: 600;
        }

        tbody td {
            padding: 3.5px 4px;
            border-bottom: 0.5px solid #e2e8f0;
            font-size: 9px;
        }

        tbody tr:nth-child(even) td { background: #f8fafc; }
        tbody tr:hover td { background: #eff6ff; }

        tfoot td {
            font-weight: bold;
            font-size: 9px;
            background: #eff6ff;
            border-top: 2px solid #0C1E3E;
            padding: 4px;
            color: #0C1E3E;
        }

        .right  { text-align: right; }
        .center { text-align: center; }

        /* ── FOOTER ── */
        .print-footer {
            margin-top: 14px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }

        @media print {
            body { padding: 0; }
            .print-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 4px 14px; }
        }
    </style>
</head>
<body>

{{-- KOP --}}
<div class="kop">
    <div class="kop-logo">✝</div>
    <div class="kop-text">
        <h1>YAYASAN PENDIDIKAN KRISTEN</h1>
        <p>Jl. Kasih No. 1, Kota | Telp: (021) 123-4567</p>
    </div>
</div>

{{-- JUDUL --}}
<div class="judul-laporan">
    Laporan Pembayaran
    @if(!empty($filter['jenjang'])) {{ $filter['jenjang'] }} @endif
    @if(!empty($filter['bulan'])) — {{ \Carbon\Carbon::createFromFormat('Y-m', $filter['bulan'])->isoFormat('MMMM Y') }} @endif
</div>

{{-- FILTER INFO --}}
<div class="filter-info">
    Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }} &nbsp;|&nbsp;
    Periode: {{ $filter['tanggal_dari'] ?? '—' }} s/d {{ $filter['tanggal_sampai'] ?? '—' }} &nbsp;|&nbsp;
    Jenjang: {{ $filter['jenjang'] ?? 'Semua' }} &nbsp;|&nbsp;
    Kelas: {{ $filter['kelas'] ?? 'Semua' }}
</div>

{{-- SUMMARY --}}
<div class="summary">
    <div class="summary-box">
        <div class="lbl">Transaksi</div>
        <div class="val">{{ $pembayaran->count() }}</div>
    </div>
    <div class="summary-box">
        <div class="lbl">Total SPP</div>
        <div class="val">Rp {{ number_format($rekap['total_nominal'], 0, ',', '.') }}</div>
    </div>
    <div class="summary-box">
        <div class="lbl">Total Mamin</div>
        <div class="val">Rp {{ number_format($rekap['total_mamin'], 0, ',', '.') }}</div>
    </div>
    <div class="summary-box grand">
        <div class="lbl">Grand Total</div>
        <div class="val">Rp {{ number_format($rekap['total_semua'], 0, ',', '.') }}</div>
    </div>
</div>

{{-- TABLE --}}
<table>
    <thead>
        <tr>
            <th class="center" style="width:22px">No</th>
            <th>Tanggal</th>
            <th>Siswa</th>
            <th class="center">Kls</th>
            <th class="center">Jenjang</th>
            <th>Bulan</th>
            <th class="right">SPP</th>
            <th class="right">Donatur</th>
            <th class="right">Mamin</th>
            <th class="right">Total</th>
            <th>Petugas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pembayaran as $idx => $p)
        <tr>
            <td class="center" style="color:#94a3b8">{{ $idx + 1 }}</td>
            <td>{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
            <td style="font-weight:600">{{ $p->siswa->nama ?? '—' }}</td>
            <td class="center">{{ $p->siswa->kelas ?? '—' }}</td>
            <td class="center">{{ $p->siswa->jenjang ?? '—' }}</td>
            <td>{{ $p->bulan_label }}</td>
            <td class="right">{{ number_format($p->nominal_per_bulan * $p->jumlah_bulan, 0, ',', '.') }}</td>
            <td class="right">{{ number_format($p->nominal_donator, 0, ',', '.') }}</td>
            <td class="right">{{ $p->nominal_mamin > 0 ? number_format($p->nominal_mamin, 0, ',', '.') : '—' }}</td>
            <td class="right" style="font-weight:700;color:#0C1E3E">
                {{ number_format($p->total_bayar, 0, ',', '.') }}
            </td>
            <td>{{ $p->user->name ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" class="right">TOTAL KESELURUHAN:</td>
            <td class="right">{{ number_format($rekap['total_nominal'], 0, ',', '.') }}</td>
            <td class="right">{{ number_format($rekap['total_donator'], 0, ',', '.') }}</td>
            <td class="right">{{ number_format($rekap['total_mamin'], 0, ',', '.') }}</td>
            <td class="right" style="font-size:10px">{{ number_format($rekap['total_semua'], 0, ',', '.') }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

{{-- PRINT FOOTER --}}
<div class="print-footer">
    <span>EduPay — Sistem Pembayaran Sekolah</span>
    <span>Dicetak {{ now()->isoFormat('D MMMM Y, HH:mm') }}</span>
</div>

</body>
</html>