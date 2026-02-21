{{-- resources/views/admin/users/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Edit User - ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Manajemen User</a></li>
    <li class="breadcrumb-item active">Edit: {{ $user->name }}</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">Edit User</h4>
        <p class="text-muted small mb-0">{{ $user->email }}</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@php
    $roleInfo = [
        'admin_yayasan' => ['label'=>'Admin Yayasan','color'=>'#7c3aed','bg'=>'#ede9fe'],
        'admin_tk'      => ['label'=>'Admin TK',     'color'=>'#db2777','bg'=>'#fce7f3'],
        'admin_sd'      => ['label'=>'Admin SD',     'color'=>'#1d4ed8','bg'=>'#dbeafe'],
        'admin_smp'     => ['label'=>'Admin SMP',    'color'=>'#059669','bg'=>'#d1fae5'],
    ];
    $ri = $roleInfo[$user->role] ?? ['label'=>$user->role,'color'=>'#64748b','bg'=>'#f1f5f9'];
@endphp

<div class="row justify-content-center">
<div class="col-lg-7">

    <div class="card">
        <div class="card-header py-3" style="background:var(--primary);color:white">
            <h6 class="mb-0 fw-bold"><i class="bi bi-pencil me-2"></i>Edit Data User</h6>
        </div>
        <div class="card-body">

            <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-600">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                           value="{{ old('name', $user->name) }}"
                           class="form-control @error('name') is-invalid @enderror"
                           required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email"
                           value="{{ old('email', $user->email) }}"
                           class="form-control @error('email') is-invalid @enderror"
                           required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">Role <span class="text-danger">*</span></label>
                    @if($user->id === auth()->id())
                        {{-- Tidak boleh ubah role diri sendiri --}}
                        <div class="form-control bg-light d-flex align-items-center gap-2">
                            <span class="badge rounded-pill px-3"
                                  style="background:{{ $ri['bg'] }};color:{{ $ri['color'] }}">
                                {{ $ri['label'] }}
                            </span>
                        </div>
                        <input type="hidden" name="role" value="{{ $user->role }}">
                        <div class="form-text text-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Tidak dapat mengubah role akun Anda sendiri.
                        </div>
                    @else
                        <select name="role"
                                class="form-select @error('role') is-invalid @enderror"
                                required>
                            <option value="admin_yayasan" {{ old('role', $user->role) == 'admin_yayasan' ? 'selected' : '' }}>Admin Yayasan (akses semua jenjang)</option>
                            <option value="admin_tk"      {{ old('role', $user->role) == 'admin_tk'      ? 'selected' : '' }}>Admin TK</option>
                            <option value="admin_sd"      {{ old('role', $user->role) == 'admin_sd'      ? 'selected' : '' }}>Admin SD</option>
                            <option value="admin_smp"     {{ old('role', $user->role) == 'admin_smp'     ? 'selected' : '' }}>Admin SMP</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @endif
                </div>

                <hr>

                <p class="small text-muted mb-3">
                    <i class="bi bi-lock me-1"></i>
                    Kosongkan field password jika tidak ingin mengubah password.
                </p>

                <div class="mb-3">
                    <label class="form-label fw-600">Password Baru</label>
                    <div class="input-group">
                        <input type="password" name="password" id="inputPassword"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Kosongkan jika tidak diubah"
                               autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary" id="togglePass">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-600">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation"
                           class="form-control"
                           placeholder="Ulangi password baru"
                           autocomplete="new-password">
                </div>

                <div class="d-flex gap-2 align-items-center">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                       class="btn btn-outline-secondary">Batal</a>
                </div>

            </form>

        </div>
    </div>

    {{-- Hapus User --}}
    @if($user->id !== auth()->id())
    <div class="card mt-3 border-danger border-opacity-25">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-600 text-danger small">Hapus User</div>
                <div class="text-muted small">Tindakan ini tidak dapat dibatalkan.</div>
            </div>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                  onsubmit="return confirm('Hapus user {{ $user->name }}?\nTindakan ini tidak dapat dibatalkan.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash me-1"></i>Hapus User
                </button>
            </form>
        </div>
    </div>
    @endif

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