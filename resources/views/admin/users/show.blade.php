{{-- resources/views/admin/users/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail User — ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Manajemen User</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('content')

@php
$roleInfo = [
    'admin_yayasan' => ['label'=>'Admin Yayasan', 'color'=>'#7c3aed', 'bg'=>'#ede9fe', 'border'=>'#c4b5fd'],
    'admin_tk'      => ['label'=>'Admin TK',      'color'=>'#db2777', 'bg'=>'#fce7f3', 'border'=>'#f9a8d4'],
    'admin_sd'      => ['label'=>'Admin SD',      'color'=>'#1d4ed8', 'bg'=>'#dbeafe', 'border'=>'#93c5fd'],
    'admin_smp'     => ['label'=>'Admin SMP',     'color'=>'#059669', 'bg'=>'#d1fae5', 'border'=>'#6ee7b7'],
];
$ri = $roleInfo[$user->role] ?? ['label'=>$user->role, 'color'=>'#64748b', 'bg'=>'#f1f5f9', 'border'=>'#e2e8f0'];
$isSelf = $user->id === auth()->id();
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color:var(--primary)">{{ $user->name }}</h4>
        <p class="text-muted small mb-0">{{ $user->email }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.edit', $user) }}"
           class="btn btn-outline-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="{{ route('admin.users.index') }}"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<div class="row g-4">

    {{-- ═══ Kolom Kiri: Info User ═══ --}}
    <div class="col-md-4">

        <div class="card mb-3">
            <div class="card-header py-3" style="background:var(--primary);color:white">
                <h6 class="mb-0 fw-bold"><i class="bi bi-person-circle me-2"></i>Informasi User</h6>
            </div>
            <div class="card-body text-center py-4">
                {{-- Avatar --}}
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width:72px;height:72px;background:{{ $ri['bg'] }};
                            color:{{ $ri['color'] }};font-size:1.8rem;font-weight:700;
                            border:3px solid {{ $ri['border'] }}">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <div class="fw-bold fs-6 mb-1">{{ $user->name }}</div>
                <div class="text-muted small mb-2">{{ $user->email }}</div>

                <span class="badge rounded-pill px-3 py-2"
                      style="background:{{ $ri['bg'] }};color:{{ $ri['color'] }};
                             border:1px solid {{ $ri['border'] }};font-size:.75rem">
                    <i class="bi bi-shield-check me-1"></i>{{ $ri['label'] }}
                </span>

                @if($isSelf)
                <div class="mt-2">
                    <span class="badge rounded-pill"
                          style="background:#eff6ff;color:#1d4ed8;font-size:.65rem">
                        Akun saya
                    </span>
                </div>
                @endif
            </div>

            <div class="card-body border-top py-3">
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted" style="width:45%">Bergabung</td>
                        <td class="fw-600">{{ $user->created_at->isoFormat('D MMM Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Terakhir update</td>
                        <td class="fw-600">{{ $user->updated_at->diffForHumans() }}</td>
                    </tr>
                </table>
            </div>

            @if(!$isSelf)
            <div class="card-footer bg-white d-flex flex-column gap-2 pt-0">
                {{-- Reset Password --}}
                <form method="POST"
                      action="{{ route('admin.users.reset-password', $user) }}"
                      onsubmit="return confirm('Reset password {{ addslashes($user->name) }} ke password default?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Password
                    </button>
                </form>

                {{-- Hapus User --}}
                <form method="POST"
                      action="{{ route('admin.users.destroy', $user) }}"
                      onsubmit="return confirm('Hapus user {{ addslashes($user->name) }}? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-trash me-1"></i>Hapus User
                    </button>
                </form>
            </div>
            @endif
        </div>

    </div>

    {{-- ═══ Kolom Kanan: Statistik & Aktivitas ═══ --}}
    <div class="col-md-8">

        {{-- Stat Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-3">
                        <div class="fw-bold fs-3 lh-1 mb-1" style="color:var(--primary)">
                            {{ $totalPembayaran }}
                        </div>
                        <div class="text-muted small">Total Pembayaran</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-3">
                        <div class="fw-bold fs-3 lh-1 mb-1 text-success">
                            {{ $totalSetoran }}
                        </div>
                        <div class="text-muted small">Total Setoran</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-3">
                        <div class="fw-bold fs-3 lh-1 mb-1 text-warning">
                            {{ $bulanIni }}
                        </div>
                        <div class="text-muted small">Bulan Ini</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transaksi Terakhir --}}
        <div class="card">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold" style="color:var(--primary)">
                    <i class="bi bi-clock-history me-2"></i>5 Pembayaran Terakhir
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Siswa</th>
                            <th>Tanggal</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPembayaran as $p)
                        <tr>
                            <td>
                                <a href="{{ route('pembayaran.show', $p) }}"
                                   class="small text-decoration-none fw-600"
                                   style="color:var(--primary)">
                                    <code>{{ $p->kode_bayar }}</code>
                                </a>
                            </td>
                            <td class="small">{{ $p->siswa->nama ?? '—' }}</td>
                            <td class="small text-muted">
                                {{ $p->tanggal_bayar->format('d/m/Y') }}
                            </td>
                            <td class="text-end small fw-600 text-success">
                                Rp {{ number_format($p->total_bayar, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted small">
                                <i class="bi bi-inbox d-block mb-1 opacity-30 fs-4"></i>
                                Belum ada transaksi.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($totalPembayaran > 5)
            <div class="card-footer bg-white text-center py-2">
                <a href="{{ route('pembayaran.index', ['user_id' => $user->id]) }}"
                   class="small text-decoration-none" style="color:var(--primary)">
                    Lihat semua {{ $totalPembayaran }} pembayaran →
                </a>
            </div>
            @endif
        </div>

    </div>

</div>

{{-- Flash: reset password result (berisi HTML bold) --}}
@if(session('success') && str_contains(session('success'), 'direset'))
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div class="toast show align-items-center border-0 shadow"
         style="background:#d1fae5;color:#065f46" role="alert">
        <div class="d-flex">
            <div class="toast-body small">
                <i class="bi bi-check-circle-fill me-2"></i>
                {!! session('success') !!}
            </div>
            <button type="button" class="btn-close me-2 m-auto"
                    data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endif

@endsection