{{-- resources/views/pembayaran/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Data Pembayaran')

@section('breadcrumb')
    <li class="breadcrumb-item active">Pembayaran</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--navy); font-family:'Sora',sans-serif;">Data Pembayaran</h4>
        <p class="mb-0" style="color: var(--ink-muted); font-size:.85rem;">Riwayat transaksi pembayaran SPP</p>
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
                <label class="form-label">Cari Siswa / Kode</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control form-control-sm"
                       placeholder="Nama siswa atau kode bayar...">
            </div>

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
                    @foreach(['TK', 'SD', 'SMP'] as $j)
                        <option value="{{ $j }}" {{ request('jenjang') == $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-md-1">
                <label class="form-label">Status</label>
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
                <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary btn-sm"
                   title="Reset filter">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>

        </form>
    </div>
</div>

{{-- Summary Bar --}}
<div class="row g-2 mb-3">
    @php
        $col = $pembayaran->getCollection();
    @endphp
    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 text-center"
             style="background:var(--surface);border:1px solid var(--border);">
            <div class="fw-bold" style="font-size:1.25rem;color:var(--navy);">
                {{ $pembayaran->total() }}
            </div>
            <div style="color:var(--ink-muted);font-size:.8rem;">Total Transaksi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 text-center"
             style="background:var(--surface);border:1px solid var(--border);">
            <div class="fw-600" style="font-size:.9rem;color:var(--green);">
                Rp {{ number_format($col->sum(fn($p) => (float)$p->total_bayar), 0, ',', '.') }}
            </div>
            <div style="color:var(--ink-muted);font-size:.8rem;">Total (halaman ini)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 text-center"
             style="background:var(--surface);border:1px solid var(--border);">
            <div class="fw-bold" style="font-size:1.15rem;color:var(--yellow);">
                {{ $col->whereNull('setoran_id')->count() }}
            </div>
            <div style="color:var(--ink-muted);font-size:.8rem;">Belum Disetor</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 text-center"
             style="background:var(--surface);border:1px solid var(--border);">
            <div class="fw-bold" style="font-size:1.15rem;color:#0369a1;">
                {{ $col->whereNotNull('setoran_id')->count() }}
            </div>
            <div style="color:var(--ink-muted);font-size:.8rem;">Sudah Disetor</div>
        </div>
    </div>
</div>

{{-- Tabel --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:45px">No</th>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Bulan</th>
                        <th class="text-end">Total</th>
                        <th>Kredit</th>
                        <th>Status Setor</th>
                        <th>Petugas</th>
                        <th class="pe-4 text-end" style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayaran as $index => $p)
                    <tr>
                        <td class="ps-4" style="color:var(--ink-faint);font-size:.8rem;">
                            {{ $pembayaran->firstItem() + $index }}
                        </td>
                        <td>
                            <code style="font-size:.8rem;color:var(--navy);">{{ $p->kode_bayar }}</code>
                        </td>
                        <td class="small text-nowrap" style="color:var(--ink-soft);">
                            {{ $p->tanggal_bayar->format('d/m/Y') }}
                        </td>
                        <td>
                            <a href="{{ route('siswa.show', $p->siswa_id) }}"
                               class="text-decoration-none fw-600"
                               style="color:var(--ink);">
                                {{ $p->siswa->nama ?? '—' }}
                            </a>
                            @if($p->siswa)
                                @php $jClass = 'badge-' . strtolower($p->siswa->jenjang); @endphp
                                <span class="{{ $jClass }} ms-1">{{ $p->siswa->jenjang }}</span>
                            @endif
                        </td>
                        <td class="small" style="color:var(--ink-soft);">
                            {{ $p->siswa->kelas ?? '—' }}
                        </td>
                        <td>
                            @php
                                $bulanArr = $p->bulan_bayar ?? [];
                                $count    = count($bulanArr);
                            @endphp
                            <span class="small" style="color:var(--ink-soft);" title="{{ $p->bulan_label }}">
                                @if($count > 2)
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $bulanArr[0])->translatedFormat('M Y') }}
                                    <span class="badge ms-1"
                                          style="background:var(--bg);color:var(--ink-muted);
                                                 border:1px solid var(--border);font-size:.65rem;">
                                        +{{ $count - 1 }}
                                    </span>
                                @elseif($count > 0)
                                    {{ $p->bulan_label }}
                                @else
                                    <span style="color:var(--ink-faint);">—</span>
                                @endif
                            </span>
                            <span class="badge ms-1"
                                  style="background:var(--blue-pale);color:var(--blue-dark);
                                         border:1px solid var(--blue-light);font-size:.65rem;">
                                {{ $p->jumlah_bulan }} bln
                            </span>
                        </td>
                        <td class="text-end fw-600 text-nowrap" style="color:var(--green);">
                            Rp {{ number_format($p->total_bayar, 0, ',', '.') }}
                        </td>
                        <td class="small text-nowrap">
                            @if(($p->kredit_digunakan ?? 0) > 0)
                                <span class="badge"
                                      style="background:#fef9c3;color:#92400e;
                                             border:1px solid #fcd34d;font-size:.72rem;"
                                      title="Kredit digunakan">
                                    −Rp {{ number_format($p->kredit_digunakan, 0, ',', '.') }}
                                </span>
                            @else
                                <span style="color:var(--ink-faint);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($p->setoran_id)
                                <a href="{{ route('setoran.show', $p->setoran_id) }}"
                                   class="badge text-decoration-none"
                                   style="background:#d1fae5;color:#059669;border:1px solid #6ee7b7;
                                          font-size:.72rem;font-weight:600;">
                                    <i class="bi bi-check-circle me-1"></i>Setor
                                </a>
                            @else
                                <span class="badge"
                                      style="background:var(--yellow-pale);color:#B45309;
                                             border:1px solid #FDE68A;font-size:.72rem;font-weight:600;">
                                    <i class="bi bi-clock me-1"></i>Menunggu
                                </span>
                            @endif
                        </td>
                        <td class="small" style="color:var(--ink-muted);">
                            {{ $p->user->name ?? '—' }}
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex gap-1 flex-nowrap justify-content-end">
                                <a href="{{ route('pembayaran.show', $p) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   style="color:var(--blue);" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(!$p->setoran_id)
                                <a href="{{ route('pembayaran.edit', $p) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   style="color:#B45309;" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('pembayaran.destroy', $p) }}"
                                      onsubmit="return confirm('Yakin hapus pembayaran {{ $p->kode_bayar }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-secondary"
                                            style="color:var(--red);" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @else
                                <button class="btn btn-sm btn-outline-secondary" disabled
                                        title="Sudah disetor, tidak bisa diedit/hapus">
                                    <i class="bi bi-lock"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <i class="bi bi-inbox d-block mb-2"
                               style="font-size:2rem;color:var(--ink-faint);"></i>
                            <p class="mb-2" style="color:var(--ink-muted);">Tidak ada data pembayaran.</p>
                            <a href="{{ route('pembayaran.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i>Input Pembayaran Pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pembayaran->hasPages())
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3 border-top"
             style="background:var(--bg);">
            <div style="color:var(--ink-muted);font-size:.82rem;">
                Menampilkan <strong>{{ $pembayaran->firstItem() }}</strong>–<strong>{{ $pembayaran->lastItem() }}</strong>
                dari <strong>{{ $pembayaran->total() }}</strong> data
            </div>
            {{ $pembayaran->links('pagination::bootstrap-5') }}
        </div>
        @endif

    </div>
</div>

@endsection