{{-- resources/views/laporan/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Laporan Pembayaran')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">Laporan Pembayaran</h4>
        <p class="text-muted small mb-0">
            Rekap data pembayaran
            @if(!empty($filter['jenjang']))
                — <span class="badge badge-{{ strtolower($filter['jenjang']) }}">{{ $filter['jenjang'] }}</span>
            @else
                — <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Semua Jenjang</span>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('laporan.pdf', array_filter(request()->all())) }}"
           class="btn btn-danger btn-sm" target="_blank">
            <i class="bi bi-file-pdf me-1"></i>Download PDF
        </a>
        <a href="{{ route('laporan.excel', array_filter(request()->all())) }}"
           class="btn btn-success btn-sm">
            <i class="bi bi-file-excel me-1"></i>Download Excel
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">

            {{-- Jenjang: hanya tampil jika admin yayasan (jenjangOptions > 1) --}}
            @if(count($jenjangOptions) > 1)
            <div class="col-md-2">
                <label class="form-label small fw-600 mb-1">Jenjang</label>
                <select name="jenjang" class="form-select form-select-sm">
                    <option value="">Semua Jenjang</option>
                    @foreach($jenjangOptions as $j)
                    <option value="{{ $j }}" {{ ($filter['jenjang'] ?? '') === $j ? 'selected' : '' }}>
                        {{ $j }}
                    </option>
                    @endforeach
                </select>
            </div>
            @else
            {{-- Admin jenjang: tampilkan label saja, kirim via hidden --}}
            <input type="hidden" name="jenjang" value="{{ $filter['jenjang'] }}">
            @endif

            <div class="col-md-2">
                <label class="form-label small fw-600 mb-1">Bulan</label>
                <input type="month" name="bulan"
                       value="{{ $filter['bulan'] ?? '' }}"
                       class="form-control form-control-sm"
                       placeholder="Semua bulan">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-600 mb-1">Kelas</label>
                <select name="kelas" class="form-select form-select-sm">
                    <option value="">Semua Kelas</option>
                    @foreach(['A','B','I','II','III','IV','V','VI','VII','VIII','IX'] as $k)
                    <option value="{{ $k }}" {{ ($filter['kelas'] ?? '') === $k ? 'selected' : '' }}>
                        Kelas {{ $k }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-600 mb-1">Tanggal Dari</label>
                <input type="date" name="tanggal_dari"
                       value="{{ $filter['tanggal_dari'] ?? '' }}"
                       class="form-control form-control-sm">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-600 mb-1">Tanggal Sampai</label>
                <input type="date" name="tanggal_sampai"
                       value="{{ $filter['tanggal_sampai'] ?? '' }}"
                       class="form-control form-control-sm">
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-search me-1"></i>Tampilkan
                </button>
                <a href="{{ route('laporan.index') }}"
                   class="btn btn-outline-secondary btn-sm" title="Reset filter">
                    <i class="bi bi-x"></i>
                </a>
            </div>

        </form>
    </div>
</div>

{{-- Info filter aktif --}}
@php
    $filterAktif = array_filter([
        $filter['jenjang']        ? 'Jenjang: '  . $filter['jenjang']                     : null,
        $filter['bulan']          ? 'Bulan: '    . $filter['bulan']                        : null,
        $filter['kelas']          ? 'Kelas: '    . $filter['kelas']                        : null,
        $filter['tanggal_dari']   ? 'Dari: '     . $filter['tanggal_dari']                 : null,
        $filter['tanggal_sampai'] ? 'Sampai: '   . $filter['tanggal_sampai']               : null,
    ]);
@endphp
@if(!empty($filterAktif))
<div class="alert alert-info alert-sm border-0 py-2 px-3 mb-3 rounded-3 d-flex align-items-center gap-2" style="background:#eff6ff">
    <i class="bi bi-funnel-fill text-primary small"></i>
    <span class="small">Filter aktif: {{ implode(' · ', $filterAktif) }}</span>
    <a href="{{ route('laporan.index') }}" class="ms-auto small text-decoration-none text-danger">
        <i class="bi bi-x me-1"></i>Hapus filter
    </a>
</div>
@endif

{{-- Rekap Cards --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Jumlah Transaksi</div>
                <div class="fw-bold fs-4 text-primary">{{ $rekap['jumlah_record'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Total SPP</div>
                <div class="fw-bold text-success small">
                    Rp {{ number_format($rekap['total_nominal'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Total Mamin</div>
                <div class="fw-bold text-info small">
                    Rp {{ number_format($rekap['total_mamin'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Grand Total</div>
                <div class="fw-bold" style="color:var(--primary)">
                    Rp {{ number_format($rekap['total_semua'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Rekap per Jenjang (hanya untuk admin yayasan & filter semua jenjang) --}}
@if(count($jenjangOptions) > 1 && empty($filter['jenjang']) && $pembayaran->isNotEmpty())
@php
    $perJenjang = $pembayaran->groupBy(fn($p) => $p->siswa->jenjang ?? '-');
    $jStyle = [
        'TK'  => ['color'=>'#db2777','bg'=>'#fce7f3','border'=>'#f9a8d4'],
        'SD'  => ['color'=>'#1d4ed8','bg'=>'#dbeafe','border'=>'#93c5fd'],
        'SMP' => ['color'=>'#059669','bg'=>'#d1fae5','border'=>'#6ee7b7'],
    ];
@endphp
<div class="row g-3 mb-3">
    @foreach(['TK','SD','SMP'] as $j)
    @if($perJenjang->has($j))
    @php $grp = $perJenjang[$j]; $js = $jStyle[$j]; @endphp
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-left:4px solid {{ $js['color'] }} !important">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge rounded-pill px-3"
                          style="background:{{ $js['bg'] }};color:{{ $js['color'] }};border:1px solid {{ $js['border'] }}">
                        {{ $j }}
                    </span>
                    <span class="small text-muted">{{ $grp->count() }} transaksi</span>
                </div>
                <div class="fw-bold" style="color:{{ $js['color'] }}">
                    Rp {{ number_format($grp->sum('total_bayar'), 0, ',', '.') }}
                </div>
                <div class="text-muted small">
                    {{ $grp->pluck('siswa_id')->unique()->count() }} siswa
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach
</div>
@endif

{{-- Rekap Per Kelas --}}
@if($perKelas->isNotEmpty())
<div class="card mb-3">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-table me-2" style="color:var(--primary)"></i>Rekap Per Kelas
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Kelas</th>
                    @if(count($jenjangOptions) > 1 && empty($filter['jenjang']))
                    <th>Jenjang</th>
                    @endif
                    <th>Jumlah Siswa</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($perKelas as $kelas => $data)
                <tr>
                    <td><strong>Kelas {{ $kelas !== '-' ? $kelas : '(tanpa kelas)' }}</strong></td>
                    @if(count($jenjangOptions) > 1 && empty($filter['jenjang']))
                    <td>
                        @php
                            // Cari jenjang dominan di kelas ini
                            $jKelas = $pembayaran->filter(fn($p) => ($p->siswa->kelas ?? '-') === $kelas)
                                        ->groupBy(fn($p) => $p->siswa->jenjang ?? '-')
                                        ->keys()->first() ?? '-';
                            $js2 = $jStyle[$jKelas] ?? ['color'=>'#64748b','bg'=>'#f1f5f9','border'=>'#e2e8f0'];
                        @endphp
                        <span class="badge rounded-pill px-2"
                              style="background:{{ $js2['bg'] }};color:{{ $js2['color'] }};border:1px solid {{ $js2['border'] }}">
                            {{ $jKelas }}
                        </span>
                    </td>
                    @endif
                    <td>{{ $data['jumlah_siswa'] }} siswa</td>
                    <td class="text-end fw-600 text-success">
                        Rp {{ number_format($data['total'], 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc">
                    <td colspan="{{ (count($jenjangOptions) > 1 && empty($filter['jenjang'])) ? 3 : 2 }}"
                        class="text-end fw-bold text-muted small">TOTAL:</td>
                    <td class="text-end fw-bold text-primary">
                        Rp {{ number_format($rekap['total_semua'], 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

{{-- Detail Transaksi --}}
<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-list-ul me-2" style="color:var(--primary)"></i>
            Detail Transaksi
            @if($pembayaran->isNotEmpty())
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1">
                {{ $pembayaran->count() }}
            </span>
            @endif
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table mb-0" style="font-size:.82rem">
            <thead>
                <tr>
                    <th style="width:32px">No</th>
                    <th>Tanggal</th>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Jenjang</th>
                    <th>Bulan</th>
                    <th class="text-end">SPP</th>
                    <th class="text-end">Donatur</th>
                    <th class="text-end">Mamin</th>
                    <th class="text-end">Total</th>
                    <th>Petugas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembayaran as $idx => $p)
                @php
                    $jn = $p->siswa->jenjang ?? 'sd';
                    $js3 = $jStyle[$jn] ?? ['color'=>'#64748b','bg'=>'#f1f5f9','border'=>'#e2e8f0'];
                @endphp
                <tr>
                    <td class="text-muted">{{ $idx + 1 }}</td>
                    <td class="text-muted">{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
                    <td class="fw-600">
                        <a href="{{ route('pembayaran.show', $p) }}"
                           class="text-decoration-none" style="color:var(--primary)">
                            {{ $p->siswa->nama ?? '-' }}
                        </a>
                    </td>
                    <td>{{ $p->siswa->kelas ?? '-' }}</td>
                    <td>
                        <span class="badge rounded-pill px-2"
                              style="background:{{ $js3['bg'] }};color:{{ $js3['color'] }};border:1px solid {{ $js3['border'] }};font-size:.65rem">
                            {{ $jn }}
                        </span>
                    </td>
                    <td>{{ $p->bulan_label }}</td>
                    <td class="text-end">{{ number_format($p->nominal_per_bulan * $p->jumlah_bulan, 0, ',', '.') }}</td>
                    <td class="text-end text-danger">
                        @if($p->nominal_donator > 0)
                            {{ number_format($p->nominal_donator, 0, ',', '.') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end text-info">
                        @if($p->nominal_mamin > 0)
                            {{ number_format($p->nominal_mamin, 0, ',', '.') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end fw-600 text-success">
                        {{ number_format($p->total_bayar, 0, ',', '.') }}
                    </td>
                    <td class="text-muted">{{ $p->user->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-30"></i>
                        <div class="fw-600 mb-1">Tidak ada data</div>
                        <div class="small">
                            @if(!empty($filterAktif))
                                Coba ubah atau reset filter di atas.
                            @else
                                Belum ada pembayaran yang dicatat.
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>

            @if($pembayaran->isNotEmpty())
            <tfoot>
                <tr class="fw-bold" style="background:#f0f4f8">
                    <td colspan="6" class="text-end text-muted small">TOTAL:</td>
                    <td class="text-end">
                        {{ number_format($rekap['total_nominal'], 0, ',', '.') }}
                    </td>
                    <td class="text-end text-danger">
                        {{ number_format($rekap['total_donator'], 0, ',', '.') }}
                    </td>
                    <td class="text-end text-info">
                        {{ number_format($rekap['total_mamin'], 0, ',', '.') }}
                    </td>
                    <td class="text-end text-success">
                        {{ number_format($rekap['total_semua'], 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection