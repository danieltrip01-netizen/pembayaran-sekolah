{{-- resources/views/setoran/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Data Setoran')

@section('breadcrumb')
    <li class="breadcrumb-item active">Setoran</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="font-family:'Sora',sans-serif;color:var(--ink)">Data Setoran</h4>
        <p class="mb-0 d-flex align-items-center gap-2 flex-wrap" style="color:var(--ink-muted);font-size:.85rem">
            <span>Rekap pembayaran yang sudah disetor</span>
            @if($tahunPelajaran)
                <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:600;
                             padding:.18rem .6rem;border-radius:999px;
                             background:#d1fae5;color:#065F46;border:1px solid #6ee7b7">
                    <i class="bi bi-calendar-check"></i>T.A. {{ $tahunPelajaran->nama }}
                </span>
            @else
                <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:600;
                             padding:.18rem .6rem;border-radius:999px;
                             background:var(--red-pale);color:var(--red);border:1px solid #fecaca">
                    <i class="bi bi-exclamation-circle"></i>Tidak ada T.A. aktif
                </span>
            @endif
        </p>
    </div>
    <a href="{{ route('setoran.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Buat Setoran
    </a>
</div>

@if(!$tahunPelajaran)
<div class="rounded-3 p-3 mb-3 d-flex align-items-center gap-3"
     style="background:#fff7ed;border:1px solid #fed7aa">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"
       style="color:var(--orange);font-size:1.2rem"></i>
    <div style="font-size:.85rem">
        <div class="fw-bold" style="color:#92400e">Tidak ada tahun pelajaran aktif</div>
        <div style="color:var(--ink-muted)">
            Data setoran tidak dapat ditampilkan.
            <a href="{{ route('tahun-pelajaran.index') }}" class="fw-bold ms-1">
                Aktifkan tahun pelajaran &#8594;
            </a>
        </div>
    </div>
</div>
@endif

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">

            <div class="col-md-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                       class="form-control form-control-sm">
            </div>

            <div class="col-md-2">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                       class="form-control form-control-sm">
            </div>

            @if(!auth()->user()->jenjang)
            <div class="col-md-2">
                <label class="form-label">Jenjang</label>
                <select name="jenjang" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach(['TK','SD','SMP'] as $j)
                        <option value="{{ $j }}" {{ request('jenjang') == $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-md-2">
                <label class="form-label">Cari Kode</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control form-control-sm" placeholder="Kode setoran...">
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                <a href="{{ route('setoran.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
            </div>

        </form>
    </div>
</div>

{{-- Summary Cards --}}
@php $jumlahSetoran = $setoran->total(); @endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center" style="border-color:var(--border)">
            <div class="card-body py-3">
                <div class="fw-bold mb-1" style="font-size:1.6rem;color:var(--navy);font-family:'Sora',sans-serif">
                    {{ $jumlahSetoran }}
                </div>
                <div style="font-size:.75rem;color:var(--ink-muted);font-weight:600;letter-spacing:.5px;text-transform:uppercase">
                    Total Setoran
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center" style="border-color:var(--border)">
            <div class="card-body py-3">
                <div class="fw-bold mb-1" style="font-size:.95rem;color:var(--green)">
                    Rp {{ number_format($totalNominalAll, 0, ',', '.') }}
                </div>
                <div style="font-size:.75rem;color:var(--ink-muted);font-weight:600;letter-spacing:.5px;text-transform:uppercase">
                    Total SPP
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center" style="border-color:var(--border)">
            <div class="card-body py-3">
                <div class="fw-bold mb-1" style="font-size:.95rem;color:#6366f1">
                    Rp {{ number_format($totalMaminAll, 0, ',', '.') }}
                </div>
                <div style="font-size:.75rem;color:var(--ink-muted);font-weight:600;letter-spacing:.5px;text-transform:uppercase">
                    Total Mamin
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center" style="background:var(--navy);border-color:var(--navy)">
            <div class="card-body py-3">
                <div class="fw-bold mb-1 text-white" style="font-size:.95rem">
                    Rp {{ number_format($grandTotalAll, 0, ',', '.') }}
                </div>
                <div style="font-size:.75rem;color:rgba(255,255,255,.55);font-weight:600;letter-spacing:.5px;text-transform:uppercase">
                    Grand Total
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabel --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width:45px">No</th>
                        <th>Kode Setoran</th>
                        <th>Tanggal</th>
                        <th>Jenjang</th>
                        <th class="text-center">Jml Transaksi</th>
                        <th class="text-end">Total SPP</th>
                        <th class="text-end">Total Mamin</th>
                        <th class="text-end">Grand Total</th>
                        <th>Petugas</th>
                        <th style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($setoran as $index => $s)
                    <tr>
                        <td style="color:var(--ink-faint);font-size:.82rem">{{ $setoran->firstItem() + $index }}</td>

                        <td>
                            <a href="{{ route('setoran.show', $s) }}"
                               class="fw-600 text-decoration-none" style="color:var(--blue)">
                                {{ $s->kode_setoran }}
                            </a>
                        </td>

                        <td style="font-size:.85rem;color:var(--ink-soft)">
                            {{ $s->tanggal_setoran->isoFormat('D MMM Y') }}
                        </td>

                        <td>
                            <span class="badge-{{ $s->jenjang }}">{{ $s->jenjang }}</span>
                        </td>

                        <td class="text-center">
                            <span style="display:inline-flex;align-items:center;gap:.3rem;
                                         font-size:.75rem;font-weight:600;padding:.25rem .65rem;
                                         border-radius:999px;background:var(--bg);
                                         color:var(--ink-muted);border:1px solid var(--border)">
                                <i class="bi bi-receipt" style="font-size:.65rem"></i>
                                {{ $s->pembayaran_count ?? $s->pembayaran->count() }}
                            </span>
                        </td>

                        <td class="text-end" style="font-size:.85rem;color:var(--ink-soft)">
                            Rp {{ number_format($s->total_nominal, 0, ',', '.') }}
                        </td>

                        <td class="text-end" style="font-size:.85rem;color:#6366f1">
                            @if($s->total_mamin > 0)
                                Rp {{ number_format($s->total_mamin, 0, ',', '.') }}
                            @else
                                <span style="color:var(--ink-faint)">—</span>
                            @endif
                        </td>

                        <td class="text-end fw-600" style="color:var(--green)">
                            Rp {{ number_format($s->total_keseluruhan, 0, ',', '.') }}
                        </td>

                        <td style="font-size:.85rem">
                            <div class="fw-600" style="color:var(--ink)">{{ $s->user->name ?? '—' }}</div>
                            @if($s->keterangan)
                                <div style="font-size:.72rem;color:var(--ink-muted)">
                                    {{ Str::limit($s->keterangan, 30) }}
                                </div>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('setoran.show', $s) }}"
                                   class="btn btn-sm btn-outline-primary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('setoran.cetak', $s) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Cetak PDF" target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <form method="POST" action="{{ route('setoran.destroy', $s) }}"
                                      onsubmit="return confirm('Hapus setoran {{ $s->kode_setoran }}?\nPembayaran terkait akan dilepas dari setoran ini.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="bi bi-wallet2 d-block mb-2" style="font-size:2rem;color:var(--ink-faint)"></i>
                            <div class="fw-600 mb-1" style="color:var(--ink-soft)">Belum ada data setoran</div>
                            <div class="mb-3" style="font-size:.85rem;color:var(--ink-muted)">
                                Mulai buat setoran pertama Anda
                            </div>
                            <a href="{{ route('setoran.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i>Buat Setoran Pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                @if($setoran->count() > 0)
                @php
                    $pageNominal = $setoran->getCollection()->sum('total_nominal');
                    $pageMamin   = $setoran->getCollection()->sum('total_mamin');
                    $pageTotal   = $setoran->getCollection()->sum('total_keseluruhan');
                @endphp
                <tfoot>
                    <tr class="fw-600" style="background:var(--bg)">
                        <td colspan="5" class="text-end" style="color:var(--ink-muted);font-size:.82rem">
                            Total (halaman ini):
                        </td>
                        <td class="text-end" style="font-size:.85rem">
                            Rp {{ number_format($pageNominal, 0, ',', '.') }}
                        </td>
                        <td class="text-end" style="font-size:.85rem;color:#6366f1">
                            Rp {{ number_format($pageMamin, 0, ',', '.') }}
                        </td>
                        <td class="text-end" style="font-size:.85rem;color:var(--green)">
                            Rp {{ number_format($pageTotal, 0, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($setoran->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <div style="font-size:.82rem;color:var(--ink-muted)">
                Menampilkan {{ $setoran->firstItem() }}–{{ $setoran->lastItem() }}
                dari {{ $setoran->total() }} setoran
            </div>
            {{ $setoran->links() }}
        </div>
        @endif

    </div>
</div>

@endsection