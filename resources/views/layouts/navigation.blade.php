{{--
    resources/views/layouts/navigation.blade.php
    ─────────────────────────────────────────────
    Sidebar navigation partial.
    Di-include oleh layouts/app.blade.php:
        @include('layouts.navigation')
--}}

{{-- ── Backdrop (mobile overlay) ─────────────────────────── --}}
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

{{-- ── Sidebar ─────────────────────────────────────────────── --}}
<nav class="sidebar" id="sidebar" aria-label="Navigasi Utama">

    {{-- Brand --}}
    <div class="sidebar-brand">
        <img src="{{ asset('img/icon-dpay.png') }}" alt="Logo" style="height: 60px; width: auto;">
        <div class="brand-text">
            <div class="brand-name">DorkasPay</div>
            <div class="brand-tagline">Sistem Pembayaran Sekolah</div>
        </div>
    </div>

    {{-- Nav links --}}
    <div class="sidebar-nav">

        <div class="nav-section-label">Utama</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="nav-icon-wrap"><i class="bi bi-house-fill" aria-hidden="true"></i></span>
            Dashboard
        </a>

        <div class="nav-section-label">Data Master</div>
        <a href="{{ route('tahun-pelajaran.index') }}"
            class="nav-link {{ request()->routeIs('tahun-pelajaran.*') ? 'active' : '' }}">
            <span class="nav-icon-wrap"><i class="bi bi-calendar-fill" aria-hidden="true"></i></span>
            Tahun Ajaran
        </a>
        <a href="{{ route('setting.index') }}" class="nav-link {{ request()->routeIs('setting.*') ? 'active' : '' }}">
            <span class="nav-icon-wrap"><i class="bi bi-building" aria-hidden="true"></i></span>
            Data Sekolah
        </a>
        <a href="{{ route('siswa.index') }}" class="nav-link {{ request()->routeIs('siswa.*') ? 'active' : '' }}">
            <span class="nav-icon-wrap"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
            Data Siswa
        </a>

        <div class="nav-section-label">Transaksi</div>
        <a href="{{ route('pembayaran.index') }}"
            class="nav-link {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}">
            <span class="nav-icon-wrap"><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
            Pembayaran
        </a>
        <a href="{{ route('setoran.index') }}" class="nav-link {{ request()->routeIs('setoran.*') ? 'active' : '' }}">
            <span class="nav-icon-wrap"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
            Setoran
        </a>
        <a href="{{ route('kredit.index') }}" class="nav-link {{ request()->routeIs('kredit.*') ? 'active' : '' }}">
            <span class="nav-icon-wrap"><i class="bi bi-coin" aria-hidden="true"></i></span>
            Kredit Siswa
        </a>

        <div class="nav-section-label">Cetak &amp; Laporan</div>
        <a href="{{ route('cetak.index') }}" class="nav-link {{ request()->routeIs('cetak.*') ? 'active' : '' }}">
            <span class="nav-icon-wrap"><i class="bi bi-printer-fill" aria-hidden="true"></i></span>
            Cetak Kartu
        </a>
        <a href="{{ route('laporan.index') }}" class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
            <span class="nav-icon-wrap"><i class="bi bi-bar-chart-fill" aria-hidden="true"></i></span>
            Laporan
        </a>

        @if (auth()->user()->isAdminYayasan())
            <div class="nav-section-label">Admin</div>
            <a href="{{ route('admin.users.index') }}"
                class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <span class="nav-icon-wrap"><i class="bi bi-person-gear" aria-hidden="true"></i></span>
                Kelola User
            </a>
        @endif

    </div>

    {{-- Footer: user info + logout --}}
    <div class="sidebar-footer">
        <div class="user-card">
            <a href="{{ route('profile.edit') }}"
                class="user-avatar text-decoration-none {{ request()->routeIs('profile.*') ? 'ring-active' : '' }}"
                title="Lihat profil" aria-label="Profil saya">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </a>
            <div class="user-details" style="min-width:0">
                <a href="{{ route('profile.edit') }}"
                    class="user-name text-decoration-none {{ request()->routeIs('profile.*') ? 'text-white' : '' }}"
                    title="Edit profil">
                    {{ auth()->user()->name }}
                </a>
                <div class="user-role-tag">{{ auth()->user()->role_label }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout" title="Keluar">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </div>

</nav>
