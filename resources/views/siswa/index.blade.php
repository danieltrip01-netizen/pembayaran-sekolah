{{-- resources/views/siswa/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Data Siswa')

@section('breadcrumb')
    <li class="breadcrumb-item active">Data Siswa</li>
@endsection

@section('content')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--navy); font-family: 'Sora', sans-serif;">Data Siswa</h4>
        <p class="mb-0" style="color: var(--ink-muted); font-size: .85rem;">
            Kelola dan pantau data pembayaran siswa
            @if($tahunPelajaran)
                &nbsp;·&nbsp;
                <span class="badge"
                      style="background:var(--blue-pale);color:var(--blue-dark);
                             border:1px solid var(--blue-light);font-size:.72rem;font-weight:600;">
                    <i class="bi bi-calendar-check me-1"></i>T.A. {{ $tahunPelajaran->nama }}
                </span>
            @else
                &nbsp;·&nbsp;
                <span class="badge"
                      style="background:#fff3cd;color:#856404;border:1px solid #ffc107;font-size:.72rem;">
                    <i class="bi bi-exclamation-triangle me-1"></i>Tidak ada tahun pelajaran aktif
                </span>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('siswa.import.index') }}" class="btn btn-outline-success btn-sm px-3">
            <i class="bi bi-file-earmark-excel me-1"></i>Import
        </a>
        <a href="{{ route('siswa.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i>Tambah Siswa
        </a>
    </div>
</div>

{{-- Filter Card --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Cari Nama / ID</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control" placeholder="Ketik nama siswa...">
                </div>
            </div>

            @if(!$jenjang)
            <div class="col-md-2">
                <label class="form-label">Jenjang</label>
                <select name="jenjang" class="form-select form-select-sm">
                    <option value="">Semua Jenjang</option>
                    @foreach(['TK','SD','SMP'] as $j)
                    <option value="{{ $j }}" {{ request('jenjang') == $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Kelas — dari tabel kelas (bukan hardcoded string) --}}
            <div class="col-md-2">
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select form-select-sm">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasOptions as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }} ({{ $k->jenjang }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="aktif"       {{ request('status') == 'aktif'       ? 'selected' : '' }}>Aktif</option>
                    <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                @if(request()->hasAny(['search','jenjang','kelas_id','status']))
                <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Main Table Card --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:50px">No</th>
                        <th>Siswa</th>
                        <th class="text-center">Kelas</th>
                        <th class="text-end">SPP/Bln</th>
                        <th class="text-end">Donatur</th>
                        <th class="text-end">Total Tagihan</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswa as $index => $s)
                    @php
                        // Ambil dari kelasAktif (sudah eager-loaded)
                        $ka = $s->kelasAktif;
                    @endphp
                    <tr>
                        <td class="ps-4" style="color: var(--ink-faint); font-size: .8rem;">
                            {{ $siswa->firstItem() + $index }}
                        </td>
                        <td>
                            <a href="{{ route('siswa.show', $s) }}"
                               class="fw-600 text-decoration-none d-block mb-0"
                               style="font-size: .9rem; color: var(--blue);">{{ $s->nama }}</a>
                            <small style="color: var(--ink-muted); font-size: .75rem;">
                                {{ $s->id_siswa }}
                            </small>
                        </td>
                        <td class="text-center">
                            @if($ka?->kelas)
                                <span class="badge rounded-pill fw-600"
                                      style="background: var(--blue-pale); color: var(--blue-dark);
                                             border: 1px solid var(--blue-light); font-size: .75rem; padding: .3rem .7rem;">
                                    {{ $ka->kelas->nama }}
                                </span>
                            @else
                                <span style="color: var(--ink-faint); font-size:.78rem;">Belum ditempatkan</span>
                            @endif
                        </td>
                        <td class="text-end fw-600" style="color: var(--ink-soft);">
                            {{ $ka ? number_format($ka->nominal_spp, 0, ',', '.') : '—' }}
                        </td>
                        <td class="text-end fw-600" style="color: var(--red);">
                            {{ ($ka && $ka->nominal_donator > 0)
                                ? number_format($ka->nominal_donator, 0, ',', '.')
                                : '—' }}
                        </td>
                        <td class="text-end fw-600" style="color: var(--navy);">
                            {{ $ka ? number_format($ka->getTagihanPerBulan(), 0, ',', '.') : '—' }}
                        </td>
                        <td class="text-center">
                            @if($s->status === 'aktif')
                                <span class="badge rounded-pill"
                                      style="background:#d1fae5; color:#065F46; border:1px solid #6EE7B7;
                                             font-size:.72rem; padding:.3rem .8rem; font-weight:600;">
                                    <i class="bi bi-dot"></i>Aktif
                                </span>
                            @else
                                <span class="badge rounded-pill"
                                      style="background:#f1f5f9; color:#64748B; border:1px solid #e2e8f0;
                                             font-size:.72rem; padding:.3rem .8rem; font-weight:600;">
                                    Non-Aktif
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('siswa.show', $s) }}"
                                   class="btn btn-outline-secondary border-0"
                                   style="color: var(--blue);" title="Detail">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <a href="{{ route('siswa.edit', $s) }}"
                                   class="btn btn-outline-secondary border-0"
                                   style="color: #B45309;" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-outline-secondary border-0"
                                        style="color: var(--red);"
                                        onclick="confirmDelete('{{ $s->id }}', '{{ addslashes($s->nama) }}')"
                                        title="Hapus">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                                <form id="delete-form-{{ $s->id }}"
                                      action="{{ route('siswa.destroy', $s) }}"
                                      method="POST" style="display:none;">
                                    @csrf @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="mb-3" style="color: var(--ink-faint);">
                                <i class="bi bi-people fs-1"></i>
                            </div>
                            <h6 class="fw-bold" style="color: var(--ink-soft);">Tidak ada data ditemukan</h6>
                            <p style="color: var(--ink-muted); font-size: .85rem;">
                                Coba sesuaikan filter atau tambahkan data baru
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($siswa->hasPages())
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top"
             style="background: var(--bg);">
            <div>
                {{ $siswa->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete(id, name) {
        if (confirm('Apakah Anda yakin ingin menghapus siswa ' + name + '?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
@endpush

@endsection