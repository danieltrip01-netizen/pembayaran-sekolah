{{-- resources/views/setoran/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail Setoran - ' . $setoran->kode_setoran)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('setoran.index') }}">Setoran</a></li>
    <li class="breadcrumb-item active">{{ $setoran->kode_setoran }}</li>
@endsection

@section('content')

@php
    $jStyle = match($setoran->jenjang) {
        'TK'    => ['color'=>'#db2777','bg'=>'#fce7f3','border'=>'#f9a8d4'],
        'SD'    => ['color'=>'#1d4ed8','bg'=>'#dbeafe','border'=>'#93c5fd'],
        'SMP'   => ['color'=>'#059669','bg'=>'#d1fae5','border'=>'#6ee7b7'],
        default => ['color'=>'#64748b','bg'=>'#f1f5f9','border'=>'#e2e8f0'],
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">{{ $setoran->kode_setoran }}</h4>
        <p class="text-muted small mb-0">
            <span class="badge rounded-pill px-3 me-1"
                  style="background:{{ $jStyle['bg'] }};color:{{ $jStyle['color'] }};border:1px solid {{ $jStyle['border'] }}">
                {{ $setoran->jenjang }}
            </span>
            {{ $setoran->tanggal_setoran->isoFormat('dddd, D MMMM Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('setoran.cetak', $setoran) }}" target="_blank"
           class="btn btn-outline-danger btn-sm">
            <i class="bi bi-printer me-1"></i>Cetak PDF
        </a>
        <a href="{{ route('setoran.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

{{-- @if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-3">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif --}}

<div class="row g-3">

    {{-- ── Info Setoran ──────────────────────────────────────────── --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header py-3" style="background:var(--primary);color:white">
                <h6 class="mb-0 fw-bold"><i class="bi bi-receipt me-2"></i>Informasi Setoran</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:100px">Kode</td>
                        <td><code class="fw-bold">{{ $setoran->kode_setoran }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal</td>
                        <td>{{ $setoran->tanggal_setoran->isoFormat('D MMM Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jenjang</td>
                        <td>
                            <span class="badge rounded-pill px-3"
                                  style="background:{{ $jStyle['bg'] }};color:{{ $jStyle['color'] }};border:1px solid {{ $jStyle['border'] }}">
                                {{ $setoran->jenjang }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Petugas</td>
                        <td class="fw-600">{{ $setoran->user->name ?? '-' }}</td>
                    </tr>
                    @if($setoran->keterangan)
                    <tr>
                        <td class="text-muted">Keterangan</td>
                        <td>{{ $setoran->keterangan }}</td>
                    </tr>
                    @endif
                </table>

                <hr class="my-3">

                {{-- Rekap Nominal --}}
                <div class="rounded-3 p-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Total SPP</span>
                        <strong>Rp {{ number_format($setoran->total_nominal, 0, ',', '.') }}</strong>
                    </div>
                    @if($setoran->total_mamin > 0)
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Total Mamin</span>
                        <strong style="color:#6366f1">Rp {{ number_format($setoran->total_mamin, 0, ',', '.') }}</strong>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Jumlah Transaksi</span>
                        <strong>{{ $setoran->pembayaran->count() }} transaksi</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold small">Grand Total</span>
                        <strong class="text-success" style="font-size:1.1rem">
                            Rp {{ number_format($setoran->total_keseluruhan, 0, ',', '.') }}
                        </strong>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white">
                <form method="POST" action="{{ route('setoran.destroy', $setoran) }}"
                      onsubmit="return confirm('Hapus setoran {{ $setoran->kode_setoran }}?\n\nSemua pembayaran akan dilepas dari setoran ini dan dapat disetor ulang.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-trash me-1"></i>Hapus Setoran Ini
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Daftar Pembayaran ─────────────────────────────────────── --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold" style="color:var(--primary)">
                    <i class="bi bi-list-check me-2"></i>Daftar Pembayaran
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1">
                        {{ $setoran->pembayaran->count() }}
                    </span>
                </h6>
                <span class="small text-muted">
                    Total: <strong class="text-success">
                        Rp {{ number_format($setoran->total_keseluruhan, 0, ',', '.') }}
                    </strong>
                </span>
            </div>
            <div class="table-responsive">
                <table class="table mb-0" style="font-size:.82rem">
                    <thead>
                        <tr>
                            <th style="width:32px">#</th>
                            <th>Kode</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Bulan</th>
                            <th class="text-end">SPP</th>
                            @if($setoran->jenjang === 'TK')
                            <th class="text-end">Mamin</th>
                            @endif
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($setoran->pembayaran as $i => $p)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ route('pembayaran.show', $p) }}"
                                   class="text-decoration-none" style="color:var(--primary)">
                                    <code>{{ $p->kode_bayar }}</code>
                                </a>
                                <div class="text-muted" style="font-size:.7rem">
                                    {{ $p->tanggal_bayar->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="fw-600">{{ $p->siswa->nama ?? '-' }}</td>
                            <td>{{ $p->siswa->kelas ?? '-' }}</td>
                            <td>
                                {{ $p->bulan_label }}
                                <div class="text-muted" style="font-size:.7rem">{{ $p->jumlah_bulan }} bulan</div>
                            </td>
                            <td class="text-end">
                                Rp {{ number_format($p->nominal_per_bulan * $p->jumlah_bulan, 0, ',', '.') }}
                            </td>
                            @if($setoran->jenjang === 'TK')
                            <td class="text-end" style="color:#6366f1">
                                @if($p->nominal_mamin > 0)
                                    Rp {{ number_format($p->nominal_mamin, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            @endif
                            <td class="text-end fw-bold text-success">
                                Rp {{ number_format($p->total_bayar, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $setoran->jenjang === 'TK' ? 8 : 7 }}"
                                class="text-center py-4 text-muted">
                                Tidak ada pembayaran dalam setoran ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                    @if($setoran->pembayaran->isNotEmpty())
                    <tfoot>
                        <tr class="fw-bold" style="background:#f8fafc">
                            <td colspan="{{ $setoran->jenjang === 'TK' ? 5 : 5 }}"
                                class="text-end text-muted small py-2">
                                Total ({{ $setoran->pembayaran->count() }} transaksi):
                            </td>
                            <td class="text-end small">
                                Rp {{ number_format($setoran->pembayaran->sum(fn($p) => $p->nominal_per_bulan * $p->jumlah_bulan), 0, ',', '.') }}
                            </td>
                            @if($setoran->jenjang === 'TK')
                            <td class="text-end small" style="color:#6366f1">
                                Rp {{ number_format($setoran->pembayaran->sum('nominal_mamin'), 0, ',', '.') }}
                            </td>
                            @endif
                            <td class="text-end text-success">
                                Rp {{ number_format($setoran->total_keseluruhan, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>
@endsection