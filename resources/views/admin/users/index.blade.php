{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Manajemen User')

@section('breadcrumb')
    <li class="breadcrumb-item active">Manajemen User</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">Manajemen User</h4>
        <p class="text-muted small mb-0">Kelola akun pengguna sistem</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus me-1"></i>Tambah User
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-3">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-3">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@php
    $roleInfo = [
        'admin_yayasan' => ['label'=>'Admin Yayasan','color'=>'#7c3aed','bg'=>'#ede9fe','border'=>'#c4b5fd','icon'=>'bi-shield-lock'],
        'admin_tk'      => ['label'=>'Admin TK',     'color'=>'#db2777','bg'=>'#fce7f3','border'=>'#f9a8d4','icon'=>'bi-flower1'],
        'admin_sd'      => ['label'=>'Admin SD',     'color'=>'#1d4ed8','bg'=>'#dbeafe','border'=>'#93c5fd','icon'=>'bi-book'],
        'admin_smp'     => ['label'=>'Admin SMP',    'color'=>'#059669','bg'=>'#d1fae5','border'=>'#6ee7b7','icon'=>'bi-mortarboard'],
    ];
@endphp

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    @foreach($roleInfo as $role => $info)
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2"
                     style="width:44px;height:44px;background:{{ $info['bg'] }}">
                    <i class="bi {{ $info['icon'] }}" style="color:{{ $info['color'] }};font-size:1.1rem"></i>
                </div>
                <div class="fw-bold fs-4" style="color:{{ $info['color'] }}">
                    {{ $users->where('role', $role)->count() }}
                </div>
                <div class="text-muted small">{{ $info['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-600 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control form-control-sm"
                       placeholder="Nama atau email...">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-600 mb-1">Role</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">Semua Role</option>
                    @foreach($roleInfo as $role => $info)
                    <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                        {{ $info['label'] }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                @if(request()->hasAny(['search','role']))
                <a href="{{ route('admin.users.index') }}"
                   class="btn btn-outline-secondary btn-sm" title="Reset">
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
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Bergabung</th>
                        <th style="width:110px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $u)
                    @php $ri = $roleInfo[$u->role] ?? ['label'=>$u->role,'color'=>'#64748b','bg'=>'#f1f5f9','border'=>'#e2e8f0']; @endphp
                    <tr>
                        <td class="text-muted small">{{ $users->firstItem() + $i }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $u) }}"
                               class="fw-600 text-decoration-none" style="color:var(--primary)">
                                {{ $u->name }}
                            </a>
                            @if($u->id === auth()->id())
                            <span class="badge ms-1"
                                  style="font-size:.6rem;background:#eff6ff;color:#1d4ed8;border:1px solid #93c5fd">
                                Saya
                            </span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $u->email }}</td>
                        <td>
                            <span class="badge rounded-pill px-3"
                                  style="background:{{ $ri['bg'] }};color:{{ $ri['color'] }};border:1px solid {{ $ri['border'] }}">
                                {{ $ri['label'] }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $u->created_at->isoFormat('D MMM Y') }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.users.edit', $u) }}"
                                   class="btn btn-xs btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                      onsubmit="return confirm('Hapus user {{ $u->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-2 d-block mb-2 opacity-30"></i>
                            Tidak ada user ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
            <div class="text-muted small">
                {{ $users->firstItem() }}–{{ $users->lastItem() }}
                dari {{ $users->total() }} user
            </div>
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.btn-xs { padding: .25rem .45rem; font-size: .75rem; }
</style>
@endpush

@endsection