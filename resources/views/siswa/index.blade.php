{{-- resources/views/siswa/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Data Siswa')

@section('breadcrumb')
    <li class="breadcrumb-item active">Data Siswa</li>
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color: var(--primary)">Data Siswa</h4>
            <p class="text-muted small mb-0">Kelola data siswa sekolah</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('siswa.import.index') }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Import Excel
            </a>
            <a href="{{ route('siswa.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Tambah Siswa
            </a>
        </div>
    </div>

    {{-- Definisi kelas per jenjang (dipakai di filter & JS) --}}
    @php
        $kelasByJenjang = [
            'TK'  => ['A', 'B'],
            'SD'  => ['I', 'II', 'III', 'IV', 'V', 'VI'],
            'SMP' => ['VII', 'VIII', 'IX'],
        ];

        // Tentukan daftar kelas yang ditampilkan:
        // - Jika user punya jenjang tetap → pakai kelas jenjangnya
        // - Jika admin & sudah pilih jenjang di filter → kelas jenjang tersebut
        // - Jika admin & belum pilih jenjang → semua kelas
        if ($jenjang) {
            $kelasTampil   = $kelasByJenjang[$jenjang] ?? [];
        } elseif (request('jenjang') && isset($kelasByJenjang[request('jenjang')])) {
            $kelasTampil   = $kelasByJenjang[request('jenjang')];
        } else {
            $kelasTampil   = ['A', 'B', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX'];
        }
    @endphp

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end" id="filterForm">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="form-control form-control-sm" placeholder="Nama / ID Siswa...">
                </div>

                @if (!$jenjang)
                    {{-- Admin: pilih jenjang dulu, kelas menyesuaikan via JS --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1">Jenjang</label>
                        <select name="jenjang" class="form-select form-select-sm" id="selectJenjang">
                            <option value="">Semua</option>
                            @foreach (['TK', 'SD', 'SMP'] as $j)
                                <option value="{{ $j }}" {{ request('jenjang') == $j ? 'selected' : '' }}>
                                    {{ $j }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Kelas</label>
                    <select name="kelas" class="form-select form-select-sm" id="selectKelas">
                        <option value="">Semua</option>
                        @foreach ($kelasTampil as $k)
                            <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>
                                {{ $k }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-search me-1"></i>Cari
                    </button>
                    @if (request()->hasAny(['search', 'jenjang', 'kelas', 'status']))
                        <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="bi bi-x"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>ID Siswa</th>
                            <th>Nama</th>
                            <th>Jenjang</th>
                            <th>Kelas</th>
                            <th class="text-end">SPP</th>
                            <th class="text-end">Donatur</th>
                            <th class="text-end">Mamin</th>
                            <th class="text-end">Tagihan/bln</th>
                            <th>Status</th>
                            <th style="width:100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswa as $index => $s)
                            <tr>
                                <td class="text-muted small">{{ $siswa->firstItem() + $index }}</td>
                                <td><code class="small" style="color:var(--primary)">{{ $s->id_siswa }}</code></td>
                                <td>
                                    <a href="{{ route('siswa.show', $s) }}" class="text-decoration-none fw-semibold">
                                        {{ $s->nama }}
                                    </a>
                                </td>
                                <td>
                                    @php
                                        $jBadge = match ($s->jenjang) {
                                            'TK'    => 'background:#fce7f3;color:#db2777;border:1px solid #f9a8d4',
                                            'SD'    => 'background:#dbeafe;color:#1d4ed8;border:1px solid #93c5fd',
                                            'SMP'   => 'background:#d1fae5;color:#059669;border:1px solid #6ee7b7',
                                            default => 'background:#f1f5f9;color:#64748b',
                                        };
                                    @endphp
                                    <span class="badge rounded-pill px-2" style="{{ $jBadge }}">
                                        {{ $s->jenjang }}
                                    </span>
                                </td>
                                <td>{{ $s->kelas }}</td>
                                <td class="text-end small">{{ number_format($s->nominal_pembayaran, 0, ',', '.') }}</td>
                                <td class="text-end small text-danger">
                                    @if ($s->nominal_donator > 0)
                                        {{ number_format($s->nominal_donator, 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end small text-info">
                                    @if ($s->jenjang === 'TK' && $s->nominal_mamin > 0)
                                        {{ number_format($s->nominal_mamin, 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold" style="color:var(--primary)">
                                    {{ number_format($s->total_tagihan, 0, ',', '.') }}
                                </td>
                                <td>
                                    @if ($s->status === 'aktif')
                                        <span class="badge" style="background:#d1fae5;color:#059669;border:1px solid #6ee7b7">
                                            <i class="bi bi-check-circle me-1"></i>Aktif
                                        </span>
                                    @else
                                        <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0">
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('siswa.show', $s) }}" class="btn btn-xs btn-outline-primary" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('siswa.edit', $s) }}" class="btn btn-xs btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('siswa.destroy', $s) }}"
                                            onsubmit="return confirm('Hapus siswa {{ addslashes($s->nama) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 opacity-30"></i>
                                    <div class="fw-semibold mb-1">Tidak ada data siswa</div>
                                    <div class="small">
                                        @if (request()->hasAny(['search', 'jenjang', 'kelas', 'status']))
                                            Coba ubah atau reset filter.
                                        @else
                                            <a href="{{ route('siswa.create') }}">Tambah siswa pertama</a>
                                            atau <a href="{{ route('siswa.import.index') }}">import dari Excel</a>.
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($siswa->total() > 0)
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top gap-2">
                    <div class="text-muted small order-2 order-md-1">
                        @if ($siswa->hasPages())
                            Menampilkan
                            <strong>{{ $siswa->firstItem() }}</strong>–<strong>{{ $siswa->lastItem() }}</strong>
                            dari <strong>{{ $siswa->total() }}</strong> siswa
                        @else
                            Menampilkan <strong>{{ $siswa->total() }}</strong> siswa
                        @endif
                    </div>
                    @if ($siswa->hasPages())
                        <div class="order-1 order-md-2">
                            {{ $siswa->onEachSide(1)->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>

@endsection

@push('scripts')
{{-- Update opsi kelas secara live saat admin mengganti pilihan jenjang --}}
@if (!$jenjang)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const kelasByJenjang = @json($kelasByJenjang);

    const selectJenjang = document.getElementById('selectJenjang');
    const selectKelas   = document.getElementById('selectKelas');

    if (!selectJenjang || !selectKelas) return;

    selectJenjang.addEventListener('change', function () {
        const chosen    = this.value;
        const daftar    = kelasByJenjang[chosen]
                            ?? ['A', 'B', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX'];

        // Reset & isi ulang opsi kelas
        selectKelas.innerHTML = '<option value="">Semua</option>';
        daftar.forEach(k => {
            const opt       = document.createElement('option');
            opt.value       = k;
            opt.textContent = k;
            selectKelas.appendChild(opt);
        });
    });
});
</script>
@endif
@endpush