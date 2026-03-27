{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Profil Saya')

@section('breadcrumb')
    <li class="breadcrumb-item active">Profil Saya</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color:var(--primary)">Profil Saya</h4>
        <p class="text-muted small mb-0">Kelola informasi akun dan keamanan</p>
    </div>
</div>

<div class="row g-4">

    {{-- ── Update Info Profil ──────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold" style="color:var(--primary)">
                    <i class="bi bi-person-circle me-2"></i>Informasi Profil
                </h6>
                <p class="text-muted small mb-0 mt-1">Perbarui nama dan alamat email akun Anda.</p>
            </div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
    </div>

    {{-- ── Update Password ─────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold" style="color:var(--primary)">
                    <i class="bi bi-shield-lock me-2"></i>Ubah Password
                </h6>
                <p class="text-muted small mb-0 mt-1">Gunakan password yang panjang dan acak agar akun tetap aman.</p>
            </div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>

    {{-- ── Hapus Akun ──────────────────────────────────────── --}}
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-danger">
                    <i class="bi bi-trash3 me-2"></i>Hapus Akun
                </h6>
                <p class="text-muted small mb-0 mt-1">
                    Setelah akun dihapus, semua data akan hilang permanen.
                </p>
            </div>
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>

</div>

@endsection