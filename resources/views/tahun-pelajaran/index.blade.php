{{-- resources/views/tahun-pelajaran/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Tahun Ajaran')

@section('breadcrumb')
    <li class="breadcrumb-item active">Tahun Ajaran</li>
@endsection

@section('content')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color:var(--navy);font-family:'Sora',sans-serif;">
            Tahun Ajaran
        </h4>
        <p class="mb-0" style="color:var(--ink-muted);font-size:.85rem;">
            Kelola periode tahun ajaran, status aktif, dan kunci data
        </p>
    </div>
    <button type="button" class="btn btn-primary btn-sm px-3"
            data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg me-1"></i>Tambah Tahun Ajaran
    </button>
</div>

{{-- Flash
@if(session('success'))
<div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4" role="alert"
     style="border:none;border-left:4px solid var(--green);background:#f0fdf4;">
    <i class="bi bi-check-circle-fill flex-shrink-0" style="color:var(--green);"></i>
    <span style="color:#065F46;">{{ session('success') }}</span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4" role="alert"
     style="border:none;border-left:4px solid var(--red);background:#fef2f2;">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0" style="color:var(--red);"></i>
    <span style="color:#991B1B;">{{ session('error') }}</span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
@endif --}}

{{-- Info Banner --}}
@php $aktif = $daftarTahun->firstWhere('is_active', true); @endphp
@if($aktif)
<div class="rounded-3 p-3 mb-4 d-flex align-items-center gap-3"
     style="background:var(--blue-pale);border:1px solid var(--blue-light);">
    <i class="bi bi-calendar-check-fill fs-5 flex-shrink-0" style="color:var(--blue-dark);"></i>
    <div>
        <div class="fw-600" style="font-size:.88rem;color:var(--blue-dark);">
            Tahun Ajaran Aktif: {{ $aktif->nama }}
        </div>
        <div style="font-size:.78rem;color:var(--ink-muted);">
            {{ $aktif->tanggal_mulai->isoFormat('D MMMM Y') }}
            &ndash;
            {{ $aktif->tanggal_selesai->isoFormat('D MMMM Y') }}
            @if($aktif->is_locked)
                <span class="ms-2" style="color:var(--orange);">
                    <i class="bi bi-lock-fill me-1"></i>Dikunci
                </span>
            @endif
        </div>
    </div>
</div>
@else
<div class="rounded-3 p-3 mb-4 d-flex align-items-center gap-3"
     style="background:#fff7ed;border:1px solid #fed7aa;">
    <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0" style="color:var(--orange);"></i>
    <div>
        <div class="fw-600" style="font-size:.88rem;color:#92400E;">Tidak ada tahun ajaran aktif</div>
        <div style="font-size:.78rem;color:var(--ink-muted);">
            Pilih salah satu tahun ajaran di bawah lalu klik "Aktifkan".
        </div>
    </div>
</div>
@endif

{{-- Tabel --}}
<div class="card">
    <div class="card-body p-0">
        @if($daftarTahun->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-calendar-x d-block mb-3" style="font-size:2.5rem;color:var(--ink-faint);"></i>
            <h6 class="fw-bold" style="color:var(--ink-soft);">Belum ada tahun ajaran</h6>
            <p style="color:var(--ink-muted);font-size:.85rem;">
                Klik tombol "Tambah Tahun Ajaran" untuk memulai.
            </p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:50px;">No</th>
                        <th>Tahun Ajaran</th>
                        <th class="text-center">Periode</th>
                        <th class="text-center">Siswa Terdaftar</th>
                        <th class="text-center">Pembayaran</th>
                        <th class="text-center">Setoran</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($daftarTahun as $i => $tp)
                    <tr class="{{ $tp->is_active ? 'table-active-row' : '' }}">
                        <td class="ps-4" style="color:var(--ink-faint);">{{ $i + 1 }}</td>

                        {{-- Nama --}}
                        <td>
                            <div class="fw-600" style="color:var(--ink);">{{ $tp->nama }}</div>
                            <div style="font-size:.72rem;color:var(--ink-faint);margin-top:.1rem;">
                                ID #{{ $tp->id }}
                            </div>
                        </td>

                        {{-- Periode --}}
                        <td class="text-center" style="color:var(--ink-muted);">
                            <div style="font-size:.8rem;">
                                {{ $tp->tanggal_mulai->isoFormat('D MMM Y') }}
                            </div>
                            <div style="color:var(--ink-faint);font-size:.72rem;">
                                s/d {{ $tp->tanggal_selesai->isoFormat('D MMM Y') }}
                            </div>
                        </td>

                        {{-- Siswa --}}
                        <td class="text-center">
                            <span class="badge rounded-pill"
                                  style="background:var(--blue-pale);color:var(--blue-dark);
                                         border:1px solid var(--blue-light);font-size:.75rem;
                                         padding:.28rem .7rem;font-weight:600;">
                                {{ number_format($tp->siswa_kelas_count) }}
                            </span>
                        </td>

                        {{-- Pembayaran --}}
                        <td class="text-center">
                            <span class="badge rounded-pill"
                                  style="background:var(--green-pale);color:#065F46;
                                         border:1px solid #6EE7B7;font-size:.75rem;
                                         padding:.28rem .7rem;font-weight:600;">
                                {{ number_format($tp->pembayaran_count) }}
                            </span>
                        </td>

                        {{-- Setoran --}}
                        <td class="text-center">
                            <span class="badge rounded-pill"
                                  style="background:var(--yellow-pale);color:#B45309;
                                         border:1px solid #FDE68A;font-size:.75rem;
                                         padding:.28rem .7rem;font-weight:600;">
                                {{ number_format($tp->setoran_count) }}
                            </span>
                        </td>

                        {{-- Status badges --}}
                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center gap-1">
                                @if($tp->is_active)
                                <span class="badge rounded-pill"
                                      style="background:#d1fae5;color:#065F46;border:1px solid #6EE7B7;
                                             font-size:.68rem;font-weight:600;padding:.22rem .65rem;">
                                    <i class="bi bi-dot"></i>Aktif
                                </span>
                                @else
                                <span class="badge rounded-pill"
                                      style="background:#f1f5f9;color:#64748B;border:1px solid #e2e8f0;
                                             font-size:.68rem;font-weight:600;padding:.22rem .65rem;">
                                    Non-Aktif
                                </span>
                                @endif

                                @if($tp->is_locked)
                                <span class="badge rounded-pill"
                                      style="background:#fff7ed;color:var(--orange);border:1px solid #fed7aa;
                                             font-size:.68rem;font-weight:600;padding:.22rem .65rem;">
                                    <i class="bi bi-lock-fill me-1"></i>Dikunci
                                </span>
                                @endif
                            </div>
                        </td>

                        {{-- Aksi --}}
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1 flex-wrap">

                                {{-- Aktifkan --}}
                                @if(!$tp->is_active)
                                <form method="POST"
                                      action="{{ route('tahun-pelajaran.activate', $tp) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-success border-0"
                                            title="Aktifkan"
                                            onclick="return confirm('Aktifkan {{ addslashes($tp->nama) }} sebagai tahun pelajaran aktif?')">
                                        <i class="bi bi-check2-circle"></i>
                                    </button>
                                </form>
                                @endif

                                {{-- Kunci / Buka Kunci --}}
                                @if($tp->is_active || $tp->is_locked)
                                <form method="POST"
                                      action="{{ route('tahun-pelajaran.toggle-lock', $tp) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-secondary border-0"
                                            title="{{ $tp->is_locked ? 'Buka Kunci' : 'Kunci Data' }}"
                                            onclick="return confirm('{{ $tp->is_locked ? 'Buka kunci' : 'Kunci' }} tahun pelajaran {{ addslashes($tp->nama) }}?')">
                                        <i class="bi {{ $tp->is_locked ? 'bi-unlock' : 'bi-lock' }}"></i>
                                    </button>
                                </form>
                                @endif

                                {{-- Edit --}}
                                @if(!$tp->is_locked)
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary border-0"
                                        style="color:#B45309;" title="Edit"
                                        onclick="bukaModalEdit({{ $tp->id }},
                                            '{{ addslashes($tp->nama) }}',
                                            '{{ $tp->tanggal_mulai->format('Y-m-d') }}',
                                            '{{ $tp->tanggal_selesai->format('Y-m-d') }}'
                                        )">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                @else
                                <button class="btn btn-sm btn-outline-secondary border-0" disabled
                                        title="Data dikunci, tidak dapat diedit">
                                    <i class="bi bi-pencil-fill" style="opacity:.3;"></i>
                                </button>
                                @endif

                                {{-- Hapus --}}
                                @if(!$tp->is_locked && !$tp->is_active)
                                <form method="POST"
                                      action="{{ route('tahun-pelajaran.destroy', $tp) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-secondary border-0"
                                            style="color:var(--red);" title="Hapus"
                                            onclick="return confirm('Hapus tahun pelajaran {{ addslashes($tp->nama) }}?\nData yang sudah ada tidak akan ikut terhapus, tapi relasi akan terputus.')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════
     MODAL: TAMBAH
══════════════════════════════════════════════ --}}
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="lblTambah">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border:none;border-radius:var(--r-xl);overflow:hidden;">
            <div class="modal-header"
                 style="background:var(--navy);border:none;padding:1rem 1.25rem;">
                <h6 class="modal-title fw-bold" id="lblTambah" style="color:#fff;">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Tahun Ajaran
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('tahun-pelajaran.store') }}">
                @csrf
                <div class="modal-body p-4">

                    {{-- Validation errors --}}
                    @if($errors->any() && !old('_edit_id'))
                    <div class="alert alert-danger py-2 mb-3" style="font-size:.82rem;">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Nama --}}
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.85rem;">
                            Nama Tahun Ajaran <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama"
                               value="{{ old('nama') }}"
                               class="form-control @error('nama') is-invalid @enderror"
                               placeholder="cth: 2024/2025"
                               maxlength="50" required autofocus autocomplete="off">
                        <div class="form-text">Format bebas, mis: <code>2024/2025</code> atau <code>TA 2024-2025</code></div>
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Periode --}}
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-600" style="font-size:.85rem;">
                                Tanggal Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_mulai"
                                   value="{{ old('tanggal_mulai') }}"
                                   class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                   required>
                            @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-600" style="font-size:.85rem;">
                                Tanggal Selesai <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_selesai"
                                   value="{{ old('tanggal_selesai') }}"
                                   class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                   required>
                            @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-3 rounded-3 p-3"
                         style="background:var(--bg);border:1px solid var(--border);font-size:.8rem;color:var(--ink-muted);">
                        <i class="bi bi-info-circle me-1"></i>
                        Tahun ajaran baru otomatis berstatus <strong>non-aktif</strong>.
                        Aktifkan setelah ditambahkan.
                    </div>

                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border);padding:.75rem 1.25rem;">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                            data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     MODAL: EDIT
══════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="lblEdit">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border:none;border-radius:var(--r-xl);overflow:hidden;">
            <div class="modal-header"
                 style="background:#B45309;border:none;padding:1rem 1.25rem;">
                <h6 class="modal-title fw-bold" id="lblEdit" style="color:#fff;">
                    <i class="bi bi-pencil-fill me-2"></i>Edit Tahun Ajaran
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formEdit" action="">
                @csrf @method('PUT')
                <input type="hidden" name="_edit_id" value="1">
                <div class="modal-body p-4">

                    {{-- Edit validation errors --}}
                    @if($errors->any() && old('_edit_id'))
                    <div class="alert alert-danger py-2 mb-3" style="font-size:.82rem;">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.85rem;">
                            Nama Tahun Ajaran <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama" id="editNama"
                               class="form-control @error('nama') is-invalid @enderror"
                               maxlength="50" required>
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-600" style="font-size:.85rem;">
                                Tanggal Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_mulai" id="editMulai"
                                   class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                   required>
                            @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-600" style="font-size:.85rem;">
                                Tanggal Selesai <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_selesai" id="editSelesai"
                                   class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                   required>
                            @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border);padding:.75rem 1.25rem;">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                            data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm px-4 text-white">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Row aktif */
    .table-active-row {
        background: linear-gradient(90deg, rgba(37,99,235,.04) 0%, transparent 100%) !important;
    }
    .table-active-row td:first-child {
        border-left: 3px solid var(--blue);
    }
</style>
@endpush

@push('scripts')
<script>
function bukaModalEdit(id, nama, mulai, selesai) {
    document.getElementById('editNama').value   = nama;
    document.getElementById('editMulai').value  = mulai;
    document.getElementById('editSelesai').value = selesai;

    // Set form action dinamis
    const base = "{{ url('tahun-pelajaran') }}";
    document.getElementById('formEdit').action = base + '/' + id;

    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}

// Buka modal Edit otomatis jika ada error validasi dari POST edit
@if($errors->any() && old('_edit_id'))
document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
});
@endif

// Buka modal Tambah otomatis jika ada error validasi dari POST tambah
@if($errors->any() && !old('_edit_id'))
document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Modal(document.getElementById('modalTambah')).show();
});
@endif
</script>
@endpush