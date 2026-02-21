{{-- resources/views/admin/users/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Tambah User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Manajemen User</a></li>
    <li class="breadcrumb-item active">Tambah User</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">Tambah User Baru</h4>
        <p class="text-muted small mb-0">Buat akun pengguna sistem baru</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row justify-content-center">
<div class="col-lg-7">

<div class="card">
    <div class="card-header py-3" style="background:var(--primary);color:white">
        <h6 class="mb-0 fw-bold"><i class="bi bi-person-plus me-2"></i>Data User Baru</h6>
    </div>
    <div class="card-body">

        <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

            <div class="mb-3">
                <label class="form-label fw-600">
                    Nama Lengkap <span class="text-danger">*</span>
                </label>
                <input type="text" name="name"
                       value="{{ old('name') }}"
                       class="form-control @error('name') is-invalid @enderror"
                       placeholder="Nama petugas / admin"
                       required autofocus>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-600">
                    Email <span class="text-danger">*</span>
                </label>
                <input type="email" name="email"
                       value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="email@sekolah.sch.id"
                       required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-600">
                    Role <span class="text-danger">*</span>
                </label>
                <select name="role"
                        class="form-select @error('role') is-invalid @enderror"
                        required>
                    <option value="">-- Pilih Role --</option>
                    <option value="admin_yayasan" {{ old('role') == 'admin_yayasan' ? 'selected' : '' }}>
                        Admin Yayasan (akses semua jenjang)
                    </option>
                    <option value="admin_tk" {{ old('role') == 'admin_tk' ? 'selected' : '' }}>
                        Admin TK
                    </option>
                    <option value="admin_sd" {{ old('role') == 'admin_sd' ? 'selected' : '' }}>
                        Admin SD
                    </option>
                    <option value="admin_smp" {{ old('role') == 'admin_smp' ? 'selected' : '' }}>
                        Admin SMP
                    </option>
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">
                    Admin Yayasan dapat mengakses semua jenjang dan fitur laporan keseluruhan.
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-600">
                    Password <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="password" name="password" id="inputPassword"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Minimal 8 karakter"
                           autocomplete="new-password"
                           required>
                    <button type="button" class="btn btn-outline-secondary" id="togglePass">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-600">
                    Konfirmasi Password <span class="text-danger">*</span>
                </label>
                <input type="password" name="password_confirmation"
                       class="form-control"
                       placeholder="Ulangi password"
                       autocomplete="new-password"
                       required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-2"></i>Simpan User
                </button>
                <a href="{{ route('admin.users.index') }}"
                   class="btn btn-outline-secondary">Batal</a>
            </div>

        </form>
    </div>
</div>

</div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('togglePass').addEventListener('click', function () {
    const inp  = document.getElementById('inputPassword');
    const icon = this.querySelector('i');
    inp.type   = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'text' ? 'bi bi-eye-slash' : 'bi bi-eye';
});
</script>
@endpush