{{-- resources/views/setoran/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Data Setoran')

@section('breadcrumb')
    <li class="breadcrumb-item active">Setoran</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">Data Setoran</h4>
        <p class="text-muted small mb-0">Rekap pembayaran yang sudah disetor</p>
    </div>
    <a href="{{ route('setoran.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Buat Setoran
    </a>
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">

            <div class="col-md-2">
                <label class="form-label small fw-600 mb-1">Dari Tanggal</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                       class="form-control form-control-sm">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                       class="form-control form-control-sm">
            </div>

            @if(!auth()->user()->jenjang)
            <div class="col-md-2">
                <label class="form-label small fw-600 mb-1">Jenjang</label>
                <select name="jenjang" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach(['TK','SD','SMP'] as $j)
                        <option value="{{ $j }}" {{ request('jenjang') == $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-md-2">
                <label class="form-label small fw-600 mb-1">Cari Kode</label>
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

{{-- Summary: gunakan variabel dari controller yang mencakup SEMUA data (bukan hanya halaman ini) --}}
@php
    $jumlahSetoran = $setoran->total();
@endphp
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 text-center" style="background:white;border:1px solid #e2e8f0">
            <div class="fw-bold fs-4 text-primary">{{ $jumlahSetoran }}</div>
            <div class="text-muted small">Total Setoran</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 text-center" style="background:white;border:1px solid #e2e8f0">
            <div class="fw-bold text-success" style="font-size:.9rem">
                Rp {{ number_format($totalNominalAll, 0, ',', '.') }}
            </div>
            <div class="text-muted small">Total SPP</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 text-center" style="background:white;border:1px solid #e2e8f0">
            <div class="fw-bold text-info" style="font-size:.9rem">
                Rp {{ number_format($totalMaminAll, 0, ',', '.') }}
            </div>
            <div class="text-muted small">Total Mamin</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 text-center"
             style="background:var(--primary);border:1px solid var(--primary)">
            <div class="fw-bold text-white" style="font-size:.9rem">
                Rp {{ number_format($grandTotalAll, 0, ',', '.') }}
            </div>
            <div class="text-white small" style="opacity:.8">Grand Total</div>
        </div>
    </div>
</div>

{{-- Tabel Setoran --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width:45px">#</th>
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
                        <td class="text-muted small">{{ $setoran->firstItem() + $index }}</td>

                        <td>
                            <a href="{{ route('setoran.show', $s) }}"
                               class="fw-bold text-decoration-none text-primary">
                                {{ $s->kode_setoran }}
                            </a>
                        </td>

                        <td class="small">
                            {{ $s->tanggal_setoran->isoFormat('D MMM Y') }}
                        </td>

                        <td>
                            @php
                                $badgeStyle = match($s->jenjang) {
                                    'TK'  => 'background:#fce7f3;color:#db2777;border:1px solid #f9a8d4',
                                    'SD'  => 'background:#dbeafe;color:#1d4ed8;border:1px solid #93c5fd',
                                    'SMP' => 'background:#d1fae5;color:#059669;border:1px solid #6ee7b7',
                                    default => '',
                                };
                            @endphp
                            <span class="badge rounded-pill" style="{{ $badgeStyle }}">
                                {{ $s->jenjang }}
                            </span>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                {{ $s->pembayaran_count ?? $s->pembayaran->count() }} transaksi
                            </span>
                        </td>

                        <td class="text-end small">
                            Rp {{ number_format($s->total_nominal, 0, ',', '.') }}
                        </td>

                        <td class="text-end small text-info">
                            @if($s->total_mamin > 0)
                                Rp {{ number_format($s->total_mamin, 0, ',', '.') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td class="text-end fw-bold text-success">
                            Rp {{ number_format($s->total_keseluruhan, 0, ',', '.') }}
                        </td>

                        <td class="small">
                            <div class="fw-600">{{ $s->user->name ?? '-' }}</div>
                            @if($s->keterangan)
                                <div class="text-muted" style="font-size:.7rem">
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
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bi bi-wallet2 fs-2 d-block mb-2"></i>
                            Belum ada data setoran.
                            <br>
                            <a href="{{ route('setoran.create') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="bi bi-plus-lg me-1"></i>Buat Setoran Pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                {{-- Footer Total --}}
                @if($setoran->count() > 0)
                @php
                    $pageNominal = $setoran->getCollection()->sum('total_nominal');
                    $pageMamin   = $setoran->getCollection()->sum('total_mamin');
                    $pageTotal   = $setoran->getCollection()->sum('total_keseluruhan');
                @endphp
                <tfoot>
                    <tr class="fw-bold" style="background:#f0f4f8">
                        <td colspan="5" class="text-end">Total (halaman ini):</td>
                        <td class="text-end">
                            Rp {{ number_format($pageNominal, 0, ',', '.') }}
                        </td>
                        <td class="text-end text-info">
                            Rp {{ number_format($pageMamin, 0, ',', '.') }}
                        </td>
                        <td class="text-end text-success">
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
            <div class="text-muted small">
                Menampilkan {{ $setoran->firstItem() }}–{{ $setoran->lastItem() }}
                dari {{ $setoran->total() }} setoran
            </div>
            {{ $setoran->links() }}
        </div>
        @endif

    </div>
</div>

@endsection