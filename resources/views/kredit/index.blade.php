{{-- resources/views/kredit/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Riwayat Kredit')

@section('breadcrumb')
    <li class="breadcrumb-item active">Riwayat Kredit</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color:var(--primary)">Riwayat Kredit</h4>
        <p class="text-muted small mb-0">Semua aktivitas kredit siswa</p>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-600 mb-1">Cari Siswa</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control form-control-sm" placeholder="Nama siswa...">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-600 mb-1">Tipe</label>
                <select name="tipe" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="tambah" {{ request('tipe')=='tambah'?'selected':'' }}>Tambah</option>
                    <option value="pakai"  {{ request('tipe')=='pakai' ?'selected':'' }}>Pakai</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                @if(request()->hasAny(['search','tipe']))
                <a href="{{ route('kredit.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-x"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0" style="font-size:.85rem">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Tipe</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-end">Saldo Sesudah</th>
                        <th>Keterangan</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @forelse($log as $i => $l)
                    <tr>
                        <td class="text-muted">{{ $log->firstItem() + $i }}</td>
                        <td class="text-muted">{{ $l->created_at->isoFormat('D MMM Y') }}</td>
                        <td>
                            <a href="{{ route('kredit.create', $l->siswa) }}"
                               class="fw-600 text-decoration-none">
                                {{ $l->siswa->nama ?? '—' }}
                            </a>
                            
                        </td>
                        <td>
                            @if($l->tipe === 'tambah')
                            <span class="badge" style="background:#d1fae5;color:#059669;border:1px solid #6ee7b7">
                                <i class="bi bi-arrow-up-circle me-1"></i>Tambah
                            </span>
                            @else
                            <span class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fcd34d">
                                <i class="bi bi-arrow-down-circle me-1"></i>Pakai
                            </span>
                            @endif
                        </td>
                        <td class="text-end fw-bold"
                            style="color:{{ $l->tipe==='tambah'?'#059669':'#b45309' }}">
                            {{ $l->tipe==='tambah' ? '+' : '-' }}Rp {{ number_format($l->jumlah,0,',','.') }}
                        </td>
                        <td class="text-end" style="color:var(--primary)">
                            Rp {{ number_format($l->saldo_sesudah,0,',','.') }}
                        </td>
                        <td>
                            {{ $l->keterangan }}
                            @if($l->pembayaran)
                            <a href="{{ route('pembayaran.show', $l->pembayaran) }}"
                                 class="text-decoration-none" style="color:var(--blue)">
                                {{ $l->pembayaran->kode_bayar }}
                              </a>
                            @endif
                        </td>
                        
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-30"></i>
                            Tidak ada riwayat kredit.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($log->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
            <div class="text-muted small">{{ $log->firstItem() }}–{{ $log->lastItem() }} dari {{ $log->total() }}</div>
            {{ $log->links() }}
        </div>
        @endif
    </div>
</div>

@endsection