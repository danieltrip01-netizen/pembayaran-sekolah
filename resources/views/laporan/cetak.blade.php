{{-- resources/views/laporan/cetak.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembayaran</title>
    <style>
        * { font-family: Arial, sans-serif; font-size: 10px; }
        body { margin: 0; padding: 10px; }

        .kop {
            text-align: center;
            border-bottom: 3px double #1B4B8A;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .kop h1 { font-size: 14px; color: #1B4B8A; margin: 0 0 3px; }
        .kop p  { margin: 2px 0; color: #555; }

        .cross { font-size: 20px; color: #1B4B8A; margin-bottom: 5px; }

        .judul-laporan {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            color: #1B4B8A;
        }

        .filter-info {
            background: #f8f9fa;
            padding: 6px 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 9px;
            color: #555;
        }

        table { width: 100%; border-collapse: collapse; }
        th { background: #1B4B8A; color: white; padding: 5px; font-size: 9px; }
        td { padding: 4px 5px; border-bottom: 0.5px solid #e0e0e0; }
        tr:nth-child(even) td { background: #f8f9fa; }
        tfoot td { font-weight: bold; background: #e8f0fe; border-top: 2px solid #1B4B8A; }

        .right { text-align: right; }
        .center { text-align: center; }

        .summary {
            margin-top: 10px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }

        .summary-box {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px;
            text-align: center;
        }

        .summary-box .lbl { color: #666; font-size: 8px; }
        .summary-box .val { font-weight: bold; color: #1B4B8A; font-size: 10px; }
    </style>
</head>
<body>

<div class="kop">
    <div class="cross">✝</div>
    <h1>YAYASAN PENDIDIKAN KRISTEN</h1>
    <p>Jl. Kasih No. 1, Kota | Telp: (021) 123-4567</p>
</div>

<div class="judul-laporan">
    Laporan Pembayaran
    @if(!empty($filter['jenjang'])) {{ $filter['jenjang'] }} @endif
    @if(!empty($filter['bulan'])) — {{ \Carbon\Carbon::createFromFormat('Y-m', $filter['bulan'])->isoFormat('MMMM Y') }} @endif
</div>

<div class="filter-info">
    Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }} |
    Periode: {{ $filter['tanggal_dari'] ?? '-' }} s/d {{ $filter['tanggal_sampai'] ?? '-' }} |
    Jenjang: {{ $filter['jenjang'] ?? 'Semua' }} |
    Kelas: {{ $filter['kelas'] ?? 'Semua' }}
</div>

<!-- Summary -->
<div class="summary" style="margin-bottom:10px">
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
    <div class="summary-box">
        <div class="lbl">Grand Total</div>
        <div class="val">Rp {{ number_format($rekap['total_semua'], 0, ',', '.') }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th class="center" style="width:25px">No</th>
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
            <td class="center">{{ $idx + 1 }}</td>
            <td>{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
            <td>{{ $p->siswa->nama ?? '-' }}</td>
            <td class="center">{{ $p->siswa->kelas ?? '-' }}</td>
            <td class="center">{{ $p->siswa->jenjang ?? '-' }}</td>
            <td>{{ $p->bulan_label }}</td>
            <td class="right">{{ number_format($p->nominal_per_bulan * $p->jumlah_bulan, 0, ',', '.') }}</td>
            <td class="right">{{ number_format($p->nominal_donator, 0, ',', '.') }}</td>
            <td class="right">{{ $p->nominal_mamin > 0 ? number_format($p->nominal_mamin, 0, ',', '.') : '-' }}</td>
            <td class="right"><strong>{{ number_format($p->total_bayar, 0, ',', '.') }}</strong></td>
            <td>{{ $p->user->name ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" class="right">TOTAL KESELURUHAN:</td>
            <td class="right">{{ number_format($rekap['total_nominal'], 0, ',', '.') }}</td>
            <td class="right">{{ number_format($rekap['total_donator'], 0, ',', '.') }}</td>
            <td class="right">{{ number_format($rekap['total_mamin'], 0, ',', '.') }}</td>
            <td class="right">{{ number_format($rekap['total_semua'], 0, ',', '.') }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

</body>
</html>