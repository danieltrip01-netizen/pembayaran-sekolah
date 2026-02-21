{{-- resources/views/pembayaran/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Data Pembayaran')

@section('breadcrumb')
    <li class="breadcrumb-item active">Pembayaran</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">Data Pembayaran</h4>
        <p class="text-muted small mb-0">Riwayat transaksi pembayaran SPP</p>
    </div>
    <a href="{{ route('pembayaran.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Input Pembayaran
    </a>
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">

            <div class="col-md-3">
                <label class="form-label small fw-600 mb-1">Cari Siswa</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control form-control-sm"
                       placeholder="Nama siswa...">
            </div>

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

            <div class="col-md-1">
                <label class="form-label small fw-600 mb-1">Status</label>
                <select name="status_setor" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="belum" {{ request('status_setor') == 'belum' ? 'selected' : '' }}>Belum Setor</option>
                    <option value="sudah" {{ request('status_setor') == 'sudah' ? 'selected' : '' }}>Sudah Setor</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
            </div>

        </form>
    </div>
</div>

{{-- Summary Bar --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 text-center" style="background:white;border:1px solid #e2e8f0">
            <div class="fw-bold fs-5 text-primary">{{ $pembayaran->total() }}</div>
            <div class="text-muted small">Total Transaksi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 text-center" style="background:white;border:1px solid #e2e8f0">
            <div class="fw-bold text-success" style="font-size:.95rem">
                Rp {{ number_format($pembayaran->getCollection()->sum('total_bayar'), 0, ',', '.') }}
            </div>
            <div class="text-muted small">Total (halaman ini)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 text-center" style="background:white;border:1px solid #e2e8f0">
            <div class="fw-bold text-warning" style="font-size:.95rem">
                {{ $pembayaran->getCollection()->whereNull('setoran_id')->count() }}
            </div>
            <div class="text-muted small">Belum Disetor</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 text-center" style="background:white;border:1px solid #e2e8f0">
            <div class="fw-bold text-info" style="font-size:.95rem">
                {{ $pembayaran->getCollection()->whereNotNull('setoran_id')->count() }}
            </div>
            <div class="text-muted small">Sudah Disetor</div>
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
                        <th style="width:45px">#</th>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Bulan</th>
                        <th class="text-end">Total</th>
                        <th>Status Setor</th>
                        <th>Petugas</th>
                        <th style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayaran as $index => $p)
                    <tr>
                        <td class="text-muted small">{{ $pembayaran->firstItem() + $index }}</td>
                        <td>
                            <code class="text-primary small">{{ $p->kode_bayar }}</code>
                        </td>
                        <td class="small">{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('siswa.show', $p->siswa_id) }}"
                               class="text-decoration-none fw-600">
                                {{ $p->siswa->nama ?? '-' }}
                            </a>
                            @if($p->siswa)
                                <span class="badge badge-{{ strtolower($p->siswa->jenjang) }} ms-1"
                                      style="font-size:.65rem">
                                    {{ $p->siswa->jenjang }}
                                </span>
                            @endif
                        </td>
                        <td>{{ $p->siswa->kelas ?? '-' }}</td>
                        <td>
                            <span class="small" title="{{ $p->bulan_label }}">
                                @php
                                    $bulanArr = $p->bulan_bayar ?? [];
                                    $count    = count($bulanArr);
                                @endphp
                                @if($count > 2)
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $bulanArr[0])->isoFormat('MMM YY') }}
                                    <span class="badge bg-secondary-subtle text-secondary ms-1">+{{ $count - 1 }}</span>
                                @else
                                    {{ $p->bulan_label }}
                                @endif
                            </span>
                            <span class="badge bg-primary-subtle text-primary ms-1"
                                  style="font-size:.65rem">
                                {{ $p->jumlah_bulan }} bln
                            </span>
                        </td>                  
                        <td class="text-end fw-600 text-success">
                            Rp {{ number_format($p->total_bayar, 0, ',', '.') }}
                        </td>
                        <td>
                            @if($p->setoran_id)
                                <a href="{{ route('setoran.show', $p->setoran_id) }}"
                                   class="badge text-decoration-none"
                                   style="background:#d1fae5;color:#059669;border:1px solid #6ee7b7">
                                    <i class="bi bi-check-circle me-1"></i>Setor
                                </a>
                            @else
                                <span class="badge"
                                      style="background:#fef3c7;color:#d97706;border:1px solid #fbbf24">
                                    <i class="bi bi-clock me-1"></i>Menunggu
                                </span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $p->user->name ?? '-' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('pembayaran.show', $p) }}"
                                   class="btn btn-sm btn-outline-primary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('pembayaran.edit', $p) }}"
                                   class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('pembayaran.destroy', $p) }}"
                                      onsubmit="return confirm('Hapus data pembayaran ini?')">
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
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Tidak ada data pembayaran.
                            <br>
                            <a href="{{ route('pembayaran.create') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="bi bi-plus-lg me-1"></i>Input Pembayaran Pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pembayaran->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Menampilkan {{ $pembayaran->firstItem() }}–{{ $pembayaran->lastItem() }}
                dari {{ $pembayaran->total() }} data
            </div>
            {{ $pembayaran->links() }}
        </div>
        @endif
    </div>
</div>

@endsection