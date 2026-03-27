{{-- resources/views/setoran/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail Setoran - ' . $setoran->kode_setoran)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('setoran.index') }}">Setoran</a></li>
    <li class="breadcrumb-item active">{{ $setoran->kode_setoran }}</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="font-family:'Sora',sans-serif;color:var(--ink)">
            {{ $setoran->kode_setoran }}
        </h4>
        <p class="mb-0 d-flex align-items-center gap-2 flex-wrap" style="font-size:.85rem;color:var(--ink-muted)">
            {{ $setoran->tanggal_setoran->isoFormat('dddd, D MMMM Y') }}
            @if($setoran->tahunPelajaran)
                <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:600;
                             padding:.18rem .6rem;border-radius:999px;
                             background:#d1fae5;color:#065F46;border:1px solid #6ee7b7">
                    <i class="bi bi-calendar-check"></i>T.A. {{ $setoran->tahunPelajaran->nama }}
                </span>
            @endif
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

<div class="row g-3">

    {{-- ── Info Setoran ──────────────────────────────────────────── --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header py-3" style="background:var(--navy)">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-receipt me-2"></i>Informasi Setoran
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0" style="font-size:.875rem">
                    <tr>
                        <td style="color:var(--ink-muted);width:100px">Kode</td>
                        <td>
                            <code class="fw-bold" style="color:var(--blue);background:var(--blue-pale);
                                  padding:.15rem .45rem;border-radius:4px">
                                {{ $setoran->kode_setoran }}
                            </code>
                        </td>
                    </tr>
                    <tr>
                        <td style="color:var(--ink-muted)">Tanggal</td>
                        <td style="color:var(--ink)">{{ $setoran->tanggal_setoran->isoFormat('D MMM Y') }}</td>
                    </tr>
                    
                    @if($setoran->keterangan)
                    <tr>
                        <td style="color:var(--ink-muted)">Keterangan</td>
                        <td style="color:var(--ink-soft)">{{ $setoran->keterangan }}</td>
                    </tr>
                    @endif
                </table>

                <hr style="border-color:var(--border);margin:1rem 0">

                {{-- Rekap Nominal --}}
                <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                    <div class="d-flex justify-content-between mb-2" style="font-size:.85rem">
                        <span style="color:var(--ink-muted)">Total SPP</span>
                        <strong style="color:var(--ink)">Rp {{ number_format($setoran->total_nominal, 0, ',', '.') }}</strong>
                    </div>
                    @if($setoran->total_mamin > 0)
                    <div class="d-flex justify-content-between mb-2" style="font-size:.85rem">
                        <span style="color:var(--ink-muted)">Total Mamin</span>
                        <strong style="color:#6366f1">Rp {{ number_format($setoran->total_mamin, 0, ',', '.') }}</strong>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between mb-2" style="font-size:.85rem">
                        <span style="color:var(--ink-muted)">Jumlah Transaksi</span>
                        <strong style="color:var(--ink)">{{ $setoran->pembayaran->count() }} transaksi</strong>
                    </div>
                    <div style="height:1px;background:var(--border);margin:.6rem 0"></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-600" style="font-size:.85rem;color:var(--ink-soft)">Grand Total</span>
                        <strong style="font-size:1.1rem;color:var(--green)">
                            Rp {{ number_format($setoran->total_keseluruhan, 0, ',', '.') }}
                        </strong>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white" style="border-top:1px solid var(--border)">
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
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                 style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0 fw-bold" style="color:var(--ink)">
                    <i class="bi bi-list-check me-2" style="color:var(--blue)"></i>Daftar Pembayaran
                    <span style="display:inline-flex;align-items:center;font-size:.72rem;font-weight:600;
                                 padding:.2rem .55rem;border-radius:999px;margin-left:.35rem;
                                 background:var(--bg);color:var(--ink-muted);border:1px solid var(--border)">
                        {{ $setoran->pembayaran->count() }}
                    </span>
                </h6>
                <span style="font-size:.83rem;color:var(--ink-muted)">
                    Total: <strong style="color:var(--green)">
                        Rp {{ number_format($setoran->total_keseluruhan, 0, ',', '.') }}
                    </strong>
                </span>
            </div>
            <div class="table-responsive" style="max-height:540px;overflow:auto">
                <table class="table mb-0" style="font-size:.82rem">
                    <thead>
                        <tr>
                            <th style="width:32px">No</th>
                            <th>Kode</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Bulan</th>
                            @if($setoran->jenjang === 'TK')
                            <th class="text-end">SPP</th>
                            <th class="text-end">Mamin</th>
                            @endif
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($setoran->pembayaran as $i => $p)
                        <tr>
                            <td style="color:var(--ink-faint)">{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ route('pembayaran.show', $p) }}"
                                   class="text-decoration-none fw-600" style="color:var(--blue)">
                                    <code style="background:var(--blue-pale);color:var(--blue);
                                                 padding:.15rem .4rem;border-radius:4px;font-size:.78rem">
                                        {{ $p->kode_bayar }}
                                    </code>
                                </a>
                                <div style="font-size:.72rem;color:var(--ink-faint);margin-top:.15rem">
                                    {{ $p->tanggal_bayar->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="fw-600" style="color:var(--ink)">{{ $p->siswa->nama ?? '—' }}</td>
                            <td style="color:var(--ink-soft)">{{ $p->siswaKelas?->kelas?->nama ?? '—' }}</td>
                            <td style="color:var(--ink-soft)">
                                {{ $p->bulan_label }}
                                <div style="font-size:.72rem;color:var(--ink-faint)">{{ $p->jumlah_bulan }} bulan</div>
                            </td>
                            @if($setoran->jenjang === 'TK')
                            <td class="text-end" style="color:var(--ink-soft)">
                                Rp {{ number_format((float)$p->total_bayar - (float)$p->nominal_mamin, 0, ',', '.') }}
                            </td>
                            <td class="text-end" style="color:#6366f1">
                                @if($p->nominal_mamin > 0)
                                    Rp {{ number_format($p->nominal_mamin, 0, ',', '.') }}
                                @else
                                    <span style="color:var(--ink-faint)">—</span>
                                @endif
                            </td>
                            @endif
                            <td class="text-end fw-bold" style="color:var(--green)">
                                Rp {{ number_format($p->total_bayar, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $setoran->jenjang === 'TK' ? 8 : 7 }}"
                                class="text-center py-4" style="color:var(--ink-muted)">
                                Tidak ada pembayaran dalam setoran ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                   
                </table>
            </div>
        </div>
    </div>

</div>
@endsection