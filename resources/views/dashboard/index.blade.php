{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
<style>
    /* Progress */
    .progress-track {
        height: 5px; border-radius: 999px;
        background: var(--border); overflow: hidden;
    }
    .progress-fill {
        height: 100%; border-radius: 999px; transition: width .7s cubic-bezier(.4,0,.2,1);
    }

    /* KPI card */
    .kpi-icon {
        width: 38px; height: 38px; border-radius: var(--r-md);
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
    .kpi-label {
        font-size: .68rem; font-weight: 700; letter-spacing: .6px;
        text-transform: uppercase; color: var(--ink-muted); margin-bottom: .22rem;
    }
    .kpi-value {
        font-family: 'Sora', sans-serif; font-weight: 700;
        color: var(--navy); line-height: 1.1;
    }
    .kpi-value.lg  { font-size: 2rem; }
    .kpi-value.md  { font-size: 1.3rem; }
    .kpi-sub {
        font-size: .73rem; color: var(--ink-muted); margin-top: .3rem;
    }
    .kpi-badge {
        font-size: .65rem; font-weight: 600; padding: .18rem .55rem;
        border-radius: 999px; white-space: nowrap;
    }

    /* Activity feed */
    .feed-item {
        display: flex; align-items: flex-start; gap: .7rem;
        padding: .6rem 1rem; border-bottom: 1px solid var(--border);
        transition: background .12s;
    }
    .feed-item:last-child { border-bottom: none; }
    .feed-item:hover { background: var(--bg); }
    .feed-dot {
        width: 7px; height: 7px; border-radius: 50%;
        flex-shrink: 0; margin-top: .38rem;
    }

    /* Quick action */
    .quick-btn {
        display: flex; flex-direction: column; align-items: center; gap: .3rem;
        padding: .7rem .4rem; border-radius: var(--r-lg);
        border: 1.5px solid var(--border); background: var(--surface);
        color: var(--ink-soft); text-decoration: none;
        font-size: .7rem; font-weight: 600; transition: all .18s; text-align: center;
    }
    .quick-btn:hover {
        border-color: var(--blue-light); background: var(--blue-pale);
        color: var(--blue-dark); transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37,99,235,.1);
    }
    .quick-btn i { font-size: 1.1rem; }

    /* Jenjang row */
    .jenjang-row { padding: .65rem 0; border-bottom: 1px solid var(--border); }
    .jenjang-row:last-child { border-bottom: none; }

    /* Stat bg watermark */
    .card-watermark {
        position: absolute; right: -8px; bottom: -8px;
        font-size: 4rem; opacity: .05; line-height: 1;
        pointer-events: none; color: currentColor;
    }
</style>
@endpush

@section('content')

@php $isAdmin = !$jenjang; @endphp

{{-- ════════════════════════════════════════════════════════════════
     HEADER
════════════════════════════════════════════════════════════════ --}}
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="font-family:'Sora',sans-serif;color:var(--ink)">
            Halo, {{ auth()->user()->nama_lengkap ?? auth()->user()->name }} 👋
        </h4>
        <div class="d-flex align-items-center gap-2" style="font-size:.83rem;color:var(--ink-muted)">
            <span>{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
            @if(!$isAdmin)
                <span style="width:4px;height:4px;border-radius:50%;background:var(--ink-faint);display:inline-block"></span>
                <span class="badge-{{ $jenjang }}">{{ $jenjang }}</span>
            @else
                <span style="width:4px;height:4px;border-radius:50%;background:var(--ink-faint);display:inline-block"></span>
                <span style="font-size:.7rem;font-weight:600;padding:.15rem .55rem;border-radius:999px;
                             background:var(--blue-pale);color:var(--blue-dark);border:1px solid var(--blue-light)">
                    Admin Yayasan
                </span>
            @endif
        </div>
    </div>
   </div>

@php
    $jmlBelum = $siswaBelumBayar->count();
    $pctLunas = $totalSiswa > 0
        ? round((($totalSiswa - $jmlBelum) / $totalSiswa) * 100)
        : 0;
@endphp

{{-- ════════════════════════════════════════════════════════════════
     LAYOUT ADMIN YAYASAN
════════════════════════════════════════════════════════════════ --}}
@if($isAdmin)

{{-- KPI Row: 4 cards --}}
<div class="row g-3 mb-3">

    {{-- Total Siswa --}}
    <div class="col-6 col-xl-3">
        <div class="card position-relative overflow-hidden">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon" style="background:var(--blue-pale);color:var(--blue-dark)">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <span class="kpi-badge" style="background:var(--blue-pale);color:var(--blue-dark);border:1px solid var(--blue-light)">
                        3 Jenjang
                    </span>
                </div>
                <div class="kpi-label">Total Siswa Aktif</div>
                <div class="kpi-value lg">{{ number_format($totalSiswa) }}</div>
                <div class="mt-2 d-flex gap-1 flex-wrap">
                    <span class="badge-TK">TK {{ $siswaPerJenjang['TK'] ?? 0 }}</span>
                    <span class="badge-SD">SD {{ $siswaPerJenjang['SD'] ?? 0 }}</span>
                    <span class="badge-SMP">SMP {{ $siswaPerJenjang['SMP'] ?? 0 }}</span>
                </div>
            </div>
            <i class="bi bi-people-fill card-watermark"></i>
        </div>
    </div>

    {{-- Total Pemasukan --}}
    <div class="col-6 col-xl-3">
        <div class="card position-relative overflow-hidden">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon" style="background:var(--yellow-pale);color:#B45309">
                        <i class="bi bi-bank2"></i>
                    </div>
                    <span class="kpi-badge" style="background:var(--yellow-pale);color:#B45309;border:1px solid #FDE68A">
                        All time
                    </span>
                </div>
                <div class="kpi-label">Total Pemasukan</div>
                <div class="kpi-value md" style="color:#B45309">
                    Rp {{ number_format($totalPemasukan, 0, ',', '.') }}
                </div>
                <div class="kpi-sub">{{ number_format($totalSetoran) }} setoran tercatat</div>
            </div>
            <i class="bi bi-bank2 card-watermark"></i>
        </div>
    </div>

    {{-- Pemasukan Bulan Ini --}}
    <div class="col-6 col-xl-3">
        <div class="card position-relative overflow-hidden">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon" style="background:var(--green-pale);color:#065F46">
                        <i class="bi bi-arrow-up-circle-fill"></i>
                    </div>
                    <span class="kpi-badge" style="background:var(--green-pale);color:#065F46;border:1px solid #6EE7B7">
                        {{ now()->isoFormat('MMM Y') }}
                    </span>
                </div>
                <div class="kpi-label">Pemasukan Bulan Ini</div>
                <div class="kpi-value md" style="color:var(--green)">
                    Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}
                </div>
                <div class="kpi-sub">{{ $transaksiHariIni }} transaksi hari ini</div>
            </div>
            <i class="bi bi-cash-stack card-watermark"></i>
        </div>
    </div>

    {{-- Belum Bayar --}}
    <div class="col-6 col-xl-3">
        <div class="card position-relative overflow-hidden">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon" style="background:var(--red-pale);color:var(--red)">
                        <i class="bi bi-exclamation-circle-fill"></i>
                    </div>
                    <span class="kpi-badge"
                          style="background:{{ $pctLunas >= 80 ? 'var(--green-pale)' : 'var(--red-pale)' }};
                                 color:{{ $pctLunas >= 80 ? '#065F46' : 'var(--red)' }};
                                 border:1px solid {{ $pctLunas >= 80 ? '#6EE7B7' : '#FECACA' }}">
                        {{ $pctLunas }}% lunas
                    </span>
                </div>
                <div class="kpi-label">Belum Bayar Bulan Ini</div>
                <div class="kpi-value lg" style="color:var(--red)">
                    {{ $jmlBelum }}
                    <span style="font-size:.9rem;color:var(--ink-muted);font-family:'DM Sans',sans-serif;font-weight:400"> siswa</span>
                </div>
                <div class="mt-2">
                    <div class="progress-track">
                        <div class="progress-fill"
                             style="width:{{ $pctLunas }}%;background:{{ $pctLunas >= 80 ? 'var(--green)' : 'var(--orange)' }}">
                        </div>
                    </div>
                    <div class="kpi-sub">{{ $totalSiswa - $jmlBelum }} dari {{ $totalSiswa }} sudah lunas</div>
                </div>
            </div>
            <i class="bi bi-exclamation-circle card-watermark"></i>
        </div>
    </div>

</div>

{{-- Row 2: Grafik (8) + Jenjang breakdown (4) --}}
<div class="row g-3 mb-3">

    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                 style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0 fw-bold" style="color:var(--ink)">
                    <i class="bi bi-graph-up me-2" style="color:var(--blue)"></i>
                    Grafik Pemasukan 12 Bulan Terakhir
                </h6>
                <span style="font-size:.78rem;color:var(--ink-muted)">
                    Total: <strong style="color:var(--green)">
                        Rp {{ number_format(array_sum($grafikData['data']), 0, ',', '.') }}
                    </strong>
                </span>
            </div>
            <div class="card-body pb-2">
                <canvas id="grafikPemasukan" height="95"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-white py-3" style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0 fw-bold" style="color:var(--ink)">
                    <i class="bi bi-bar-chart-fill me-2" style="color:var(--blue)"></i>
                    Pemasukan per Jenjang
                </h6>
            </div>
            <div class="card-body d-flex flex-column justify-content-between">
                @php
                    $jData = [
                        'TK'  => ['color'=>'#F59E0B','jumlah'=>$pemasukanPerJenjang['TK']  ?? 0],
                        'SD'  => ['color'=>'var(--blue)','jumlah'=>$pemasukanPerJenjang['SD']  ?? 0],
                        'SMP' => ['color'=>'var(--green)','jumlah'=>$pemasukanPerJenjang['SMP'] ?? 0],
                    ];
                    $totalJ = array_sum(array_column($jData,'jumlah')) ?: 1;
                    $maxJ   = max(array_column($jData,'jumlah')) ?: 1;
                @endphp
                @foreach($jData as $j => $d)
                <div class="jenjang-row">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-{{ $j }}">{{ $j }}</span>
                            <span style="font-size:.72rem;color:var(--ink-muted)">
                                {{ $siswaPerJenjang[$j] ?? 0 }} siswa
                            </span>
                        </div>
                        <span style="font-size:.8rem;font-weight:600;color:var(--ink-soft)">
                            Rp {{ number_format($d['jumlah'], 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill"
                             style="width:{{ round($d['jumlah']/$maxJ*100) }}%;background:{{ $d['color'] }}">
                        </div>
                    </div>
                    <div style="font-size:.7rem;color:var(--ink-faint);margin-top:.22rem;text-align:right">
                        {{ $totalJ > 0 ? round($d['jumlah']/$totalJ*100) : 0 }}% dari total
                    </div>
                </div>
                @endforeach

                <div class="mt-3">
                    <div style="font-size:.68rem;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                                color:var(--ink-muted);margin-bottom:.6rem">Aksi Cepat</div>
                    <div class="row g-2">
                        <div class="col-4">
                            <a href="{{ route('laporan.index') }}" class="quick-btn w-100">
                                <i class="bi bi-bar-chart" style="color:#6366f1"></i>Laporan
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="{{ route('setoran.index') }}" class="quick-btn w-100">
                                <i class="bi bi-wallet2" style="color:var(--blue)"></i>Setoran
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="{{ route('siswa.index') }}" class="quick-btn w-100">
                                <i class="bi bi-people" style="color:var(--navy)"></i>Siswa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Row 3: Belum Bayar (5) + Setoran Terbaru (7) --}}
<div class="row g-3">

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                 style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0 fw-bold" style="color:var(--red)">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Belum Bayar {{ now()->isoFormat('MMMM Y') }}
                    <span style="font-size:.68rem;font-weight:600;padding:.15rem .5rem;border-radius:999px;
                                 background:var(--red-pale);color:var(--red);border:1px solid #FECACA;margin-left:.3rem">
                        {{ $jmlBelum }}
                    </span>
                </h6>
            </div>
            <div style="max-height:320px;overflow-y:auto">
                @forelse($siswaBelumBayar as $s)
                <div class="feed-item">
                    <div style="width:30px;height:30px;border-radius:var(--r-md);background:var(--navy);
                                color:#fff;font-size:.72rem;font-weight:700;flex-shrink:0;
                                display:flex;align-items:center;justify-content:center">
                        {{ strtoupper(substr($s->nama, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-600 text-truncate" style="font-size:.82rem;color:var(--ink)">{{ $s->nama }}</div>
                        <div style="font-size:.7rem;color:var(--ink-muted)">
                            <span class="badge-{{ $s->jenjang }}">{{ $s->jenjang }}</span>
                            Kelas {{ $s->kelas }}
                        </div>
                    </div>
                    <a href="{{ route('pembayaran.create', ['siswa_id' => $s->id]) }}"
                       style="font-size:.65rem;font-weight:600;padding:.2rem .55rem;border-radius:999px;
                              border:1px solid var(--blue-light);color:var(--blue);background:var(--blue-pale);
                              text-decoration:none;flex-shrink:0;white-space:nowrap">
                        Bayar
                    </a>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="bi bi-check-circle-fill d-block mb-2" style="font-size:2rem;color:var(--green)"></i>
                    <div class="fw-600" style="font-size:.88rem;color:var(--ink-soft)">Semua sudah lunas!</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                 style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0 fw-bold" style="color:var(--ink)">
                    <i class="bi bi-wallet2 me-2" style="color:var(--blue)"></i>Setoran Terbaru
                </h6>
                <a href="{{ route('setoran.index') }}" class="btn btn-sm btn-outline-primary"
                   style="font-size:.7rem;padding:.18rem .6rem">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0" style="font-size:.83rem">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Jenjang</th>
                            <th class="text-end">Grand Total</th>
                            <th>Petugas</th>
                            <th style="width:48px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($setoranTerbaru as $s)
                        <tr>
                            <td class="fw-600" style="color:var(--ink)">{{ $s->kode_setoran }}</td>
                            <td style="color:var(--ink-soft)">{{ $s->tanggal_setoran->format('d/m/Y') }}</td>
                            <td><span class="badge-{{ $s->jenjang }}">{{ $s->jenjang }}</span></td>
                            <td class="text-end fw-600" style="color:var(--green)">
                                Rp {{ number_format($s->total_keseluruhan, 0, ',', '.') }}
                            </td>
                            <td style="color:var(--ink-soft)">{{ $s->user->name ?? '—' }}</td>
                            <td>
                                <a href="{{ route('setoran.show', $s) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4" style="color:var(--ink-muted)">
                                Belum ada setoran.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     LAYOUT PETUGAS JENJANG
════════════════════════════════════════════════════════════════ --}}
@else

{{-- KPI Row: 4 kartu khusus petugas --}}
<div class="row g-3 mb-3">

    {{-- Siswa Jenjang --}}
    <div class="col-6 col-xl-3">
        <div class="card position-relative overflow-hidden">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon" style="background:var(--blue-pale);color:var(--blue-dark)">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <span class="badge-{{ $jenjang }}">{{ $jenjang }}</span>
                </div>
                <div class="kpi-label">Siswa Aktif {{ $jenjang }}</div>
                <div class="kpi-value lg">{{ number_format($totalSiswa) }}</div>
                <div class="mt-2">
                    <div class="progress-track">
                        <div class="progress-fill" style="width:{{ $pctLunas }}%;background:var(--green)"></div>
                    </div>
                    <div class="kpi-sub">{{ $totalSiswa - $jmlBelum }} dari {{ $totalSiswa }} lunas bulan ini</div>
                </div>
            </div>
            <i class="bi bi-people-fill card-watermark"></i>
        </div>
    </div>

    {{-- Pemasukan Bulan Ini --}}
    <div class="col-6 col-xl-3">
        <div class="card position-relative overflow-hidden">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon" style="background:var(--green-pale);color:#065F46">
                        <i class="bi bi-arrow-up-circle-fill"></i>
                    </div>
                    <span class="kpi-badge" style="background:var(--green-pale);color:#065F46;border:1px solid #6EE7B7">
                        {{ now()->isoFormat('MMM Y') }}
                    </span>
                </div>
                <div class="kpi-label">Pemasukan Bulan Ini</div>
                <div class="kpi-value md" style="color:var(--green)">
                    Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}
                </div>
                <div class="kpi-sub">{{ $transaksiHariIni }} transaksi hari ini</div>
            </div>
            <i class="bi bi-cash-stack card-watermark"></i>
        </div>
    </div>

    {{-- Belum Disetor --}}
    <div class="col-6 col-xl-3">
        <div class="card position-relative overflow-hidden">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon" style="background:var(--orange-pale);color:var(--orange)">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    @if($belumDisetorCount > 0)
                    <a href="{{ route('setoran.create') }}"
                       style="font-size:.65rem;font-weight:600;padding:.2rem .55rem;border-radius:999px;
                              border:1px solid #FED7AA;color:var(--orange);background:var(--orange-pale);
                              text-decoration:none">
                        Setor Sekarang
                    </a>
                    @endif
                </div>
                <div class="kpi-label">Belum Disetor</div>
                <div class="kpi-value lg" style="color:var(--orange)">
                    {{ $belumDisetorCount }}
                    <span style="font-size:.9rem;color:var(--ink-muted);font-family:'DM Sans',sans-serif;font-weight:400"> transaksi</span>
                </div>
                <div class="kpi-sub">
                    @if($belumDisetorNominal > 0)
                        Rp {{ number_format($belumDisetorNominal, 0, ',', '.') }}
                    @else
                        Semua sudah disetor
                    @endif
                </div>
            </div>
            <i class="bi bi-clock-history card-watermark"></i>
        </div>
    </div>

    {{-- Belum Bayar --}}
    <div class="col-6 col-xl-3">
        <div class="card position-relative overflow-hidden">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon" style="background:var(--red-pale);color:var(--red)">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                    <span class="kpi-badge"
                          style="background:{{ $pctLunas >= 80 ? 'var(--green-pale)' : 'var(--red-pale)' }};
                                 color:{{ $pctLunas >= 80 ? '#065F46' : 'var(--red)' }};
                                 border:1px solid {{ $pctLunas >= 80 ? '#6EE7B7' : '#FECACA' }}">
                        {{ $pctLunas }}% lunas
                    </span>
                </div>
                <div class="kpi-label">Belum Bayar Bulan Ini</div>
                <div class="kpi-value lg" style="color:var(--red)">
                    {{ $jmlBelum }}
                    <span style="font-size:.9rem;color:var(--ink-muted);font-family:'DM Sans',sans-serif;font-weight:400"> siswa</span>
                </div>
                <div class="kpi-sub">dari {{ $totalSiswa }} total siswa</div>
            </div>
            <i class="bi bi-person-x-fill card-watermark"></i>
        </div>
    </div>

</div>

{{-- Row 2: Grafik (8) + Aksi Cepat (4) --}}
<div class="row g-3 mb-3">

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                 style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0 fw-bold" style="color:var(--ink)">
                    <i class="bi bi-graph-up me-2" style="color:var(--blue)"></i>
                    Pemasukan {{ $jenjang }} — 12 Bulan Terakhir
                </h6>
                <span style="font-size:.78rem;color:var(--ink-muted)">
                    Total: <strong style="color:var(--green)">
                        Rp {{ number_format(array_sum($grafikData['data']), 0, ',', '.') }}
                    </strong>
                </span>
            </div>
            <div class="card-body pb-2">
                <canvas id="grafikPemasukan" height="95"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-white py-3" style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0 fw-bold" style="color:var(--ink)">
                    <i class="bi bi-lightning-fill me-2" style="color:#B45309"></i>Aksi Cepat
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('pembayaran.create') }}" class="quick-btn w-100">
                            <i class="bi bi-cash-coin" style="color:var(--green)"></i>Catat Bayar
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('setoran.create') }}" class="quick-btn w-100">
                            <i class="bi bi-wallet2" style="color:var(--blue)"></i>Buat Setoran
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('pembayaran.index') }}" class="quick-btn w-100">
                            <i class="bi bi-list-check" style="color:var(--navy)"></i>Riwayat Pembayaran
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('setoran.index') }}" class="quick-btn w-100">
                            <i class="bi bi-archive" style="color:#6366f1"></i>Riwayat Setor
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('cetak.index') }}" class="quick-btn w-100">
                            <i class="bi bi-printer" style="color:#B45309"></i>Cetak Kartu
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('siswa.index') }}" class="quick-btn w-100">
                            <i class="bi bi-people" style="color:var(--ink-soft)"></i>Data Siswa
                        </a>
                    </div>
                </div>

                {{-- Reminder belum disetor --}}
                @if($belumDisetorCount > 0)
                <div class="rounded-3 p-3 mt-3"
                     style="background:var(--orange-pale);border:1px solid #FED7AA">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-bell-fill mt-1 flex-shrink-0" style="color:var(--orange);font-size:.85rem"></i>
                        <div>
                            <div style="font-size:.78rem;font-weight:600;color:#92400E">
                                {{ $belumDisetorCount }} transaksi belum disetor
                            </div>
                            <div style="font-size:.72rem;color:#B45309;margin-top:.2rem">
                                Total Rp {{ number_format($belumDisetorNominal, 0, ',', '.') }}
                            </div>
                            <a href="{{ route('setoran.create') }}"
                               style="font-size:.72rem;font-weight:600;color:var(--orange);margin-top:.35rem;display:inline-block">
                                Setor sekarang →
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- Row 3: Belum Bayar (5) + Pembayaran Terbaru (4) + Setoran Terbaru (3) --}}
<div class="row g-3">

    {{-- Siswa Belum Bayar --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                 style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0 fw-bold" style="color:var(--red)">
                    <i class="bi bi-person-x-fill me-1"></i>
                    Belum Bayar {{ now()->isoFormat('MMM Y') }}
                    <span style="font-size:.68rem;font-weight:600;padding:.15rem .5rem;border-radius:999px;
                                 background:var(--red-pale);color:var(--red);border:1px solid #FECACA;margin-left:.3rem">
                        {{ $jmlBelum }}
                    </span>
                </h6>
                <div class="progress-track" style="width:80px">
                    <div class="progress-fill"
                         style="width:{{ $pctLunas }}%;background:{{ $pctLunas >= 80 ? 'var(--green)' : 'var(--orange)' }}">
                    </div>
                </div>
            </div>
            <div style="max-height:320px;overflow-y:auto">
                @forelse($siswaBelumBayar as $s)
                <div class="feed-item">
                    <div style="width:30px;height:30px;border-radius:var(--r-md);background:var(--navy);
                                color:#fff;font-size:.72rem;font-weight:700;flex-shrink:0;
                                display:flex;align-items:center;justify-content:center">
                        {{ strtoupper(substr($s->nama, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-600 text-truncate" style="font-size:.82rem;color:var(--ink)">{{ $s->nama }}</div>
                        <div style="font-size:.7rem;color:var(--ink-muted)">Kelas {{ $s->kelas }}</div>
                    </div>
                    <a href="{{ route('pembayaran.create', ['siswa_id' => $s->id]) }}"
                       style="font-size:.65rem;font-weight:600;padding:.2rem .55rem;border-radius:999px;
                              border:1px solid var(--blue-light);color:var(--blue);background:var(--blue-pale);
                              text-decoration:none;flex-shrink:0;white-space:nowrap">
                        Bayar
                    </a>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="bi bi-check-circle-fill d-block mb-2" style="font-size:2rem;color:var(--green)"></i>
                    <div class="fw-600" style="font-size:.88rem;color:var(--ink-soft)">Semua sudah lunas!</div>
                    <div style="font-size:.78rem;color:var(--ink-muted)">Tidak ada tunggakan bulan ini.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Pembayaran Terbaru --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                 style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0 fw-bold" style="color:var(--ink)">
                    <i class="bi bi-receipt me-2" style="color:var(--blue)"></i>Pembayaran Terbaru
                </h6>
                <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-outline-primary"
                   style="font-size:.7rem;padding:.18rem .6rem">Semua</a>
            </div>
            <div style="max-height:320px;overflow-y:auto">
                @forelse($pembayaranTerbaru as $p)
                <div class="feed-item">
                    <div class="feed-dot" style="background:var(--green)"></div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-600 text-truncate" style="font-size:.8rem;color:var(--ink)">
                            {{ $p->siswa->nama ?? '—' }}
                        </div>
                        <div style="font-size:.7rem;color:var(--ink-muted)">
                            {{ $p->bulan_label }} &mdash; {{ $p->tanggal_bayar->format('d/m/Y') }}
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="fw-600" style="font-size:.8rem;color:var(--green)">
                            Rp {{ number_format($p->total_bayar, 0, ',', '.') }}
                        </div>
                        @if($p->setoran_id)
                            <span style="font-size:.62rem;color:var(--ink-faint)">✓ Disetor</span>
                        @else
                            <span style="font-size:.62rem;color:var(--orange)">Belum disetor</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-5" style="color:var(--ink-muted)">
                    <i class="bi bi-inbox d-block mb-1" style="font-size:1.5rem;color:var(--ink-faint)"></i>
                    <div style="font-size:.85rem">Belum ada pembayaran.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Setoran Terbaru --}}
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                 style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0 fw-bold" style="color:var(--ink)">
                    <i class="bi bi-wallet2 me-2" style="color:var(--blue)"></i>Setoran Terbaru
                </h6>
                <a href="{{ route('setoran.index') }}" class="btn btn-sm btn-outline-primary"
                   style="font-size:.7rem;padding:.18rem .6rem">Semua</a>
            </div>
            <div style="max-height:320px;overflow-y:auto">
                @forelse($setoranTerbaru as $s)
                <a href="{{ route('setoran.show', $s) }}" class="feed-item text-decoration-none d-flex">
                    <div class="feed-dot" style="background:var(--blue)"></div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-600 text-truncate" style="font-size:.8rem;color:var(--ink)">
                            {{ $s->kode_setoran }}
                        </div>
                        <div style="font-size:.7rem;color:var(--ink-muted)">
                            {{ $s->tanggal_setoran->format('d/m/Y') }}
                        </div>
                        <div class="fw-600" style="font-size:.78rem;color:var(--green)">
                            Rp {{ number_format($s->total_keseluruhan, 0, ',', '.') }}
                        </div>
                    </div>
                </a>
                @empty
                <div class="text-center py-5" style="color:var(--ink-muted)">
                    <i class="bi bi-wallet2 d-block mb-1" style="font-size:1.5rem;color:var(--ink-faint)"></i>
                    <div style="font-size:.85rem">Belum ada setoran.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@endif {{-- end role split --}}

@endsection

@push('scripts')
<script>
const ctx        = document.getElementById('grafikPemasukan').getContext('2d');
const grafikData = @json($grafikData);

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: grafikData.labels,
        datasets: [{
            label: 'Pemasukan',
            data: grafikData.data,
            backgroundColor: function(context) {
                const { ctx: c, chartArea } = context.chart;
                if (!chartArea) return 'rgba(37,99,235,.7)';
                const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                g.addColorStop(0, 'rgba(37,99,235,.88)');
                g.addColorStop(1, 'rgba(12,30,62,.5)');
                return g;
            },
            borderColor: 'transparent',
            borderRadius: 5,
            borderSkipped: false,
            hoverBackgroundColor: 'rgba(37,99,235,.98)',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0C1E3E',
                titleFont: { size: 11, family: 'DM Sans' },
                bodyFont: { size: 12, family: 'DM Sans', weight: '600' },
                padding: 10, cornerRadius: 8,
                callbacks: {
                    label: c => '  Rp ' + new Intl.NumberFormat('id-ID').format(c.raw)
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,.04)', drawBorder: false },
                border: { display: false },
                ticks: {
                    font: { size: 10, family: 'DM Sans' }, color: '#94A3B8',
                    maxTicksLimit: 5,
                    callback: val => {
                        if (val >= 1000000) return (val/1000000).toFixed(0) + 'jt';
                        if (val >= 1000)    return (val/1000).toFixed(0) + 'rb';
                        return val;
                    }
                }
            },
            x: {
                grid: { display: false }, border: { display: false },
                ticks: { font: { size: 10, family: 'DM Sans' }, color: '#94A3B8' }
            }
        }
    }
});
</script>
@endpush