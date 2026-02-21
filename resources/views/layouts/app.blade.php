<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Pembayaran Sekolah') }} - @yield('title', 'Dashboard')</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1B4B8A;
            --primary-dark: #0f3060;
            --primary-light: #2d6cb5;
            --gold: #C5A028;
            --gold-light: #E8C547;
            --sidebar-w: 260px;
        }

        * {
            font-family: 'Nunito', sans-serif;
        }

        body {
            background: #f0f4f8;
            min-height: 100vh;
        }

        /* === SIDEBAR === */
        .sidebar {
            width: var(--sidebar-w);
            height: 100vh;
            background: linear-gradient(180deg, var(--primary-dark) 0%, var(--primary) 60%, var(--primary-light) 100%);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform .3s ease;
            box-shadow: 4px 0 15px rgba(0, 0, 0, .2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            /* Custom scrollbar */
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, .2) transparent;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .2);
            border-radius: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, .35);
        }

        .sidebar-brand {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, .15);
            text-align: center;
        }

        .sidebar-brand .brand-icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            border-radius: .875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto .75rem;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .3);
        }

        .sidebar-brand h6 {
            color: white;
            font-size: .8rem;
            font-weight: 600;
            letter-spacing: .5px;
            margin: 0;
            line-height: 1.4;
        }

        .sidebar-brand small {
            color: rgba(255, 255, 255, .6);
            font-size: .7rem;
        }

        .nav-section-title {
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .5);
            padding: .75rem 1.25rem .25rem;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, .75);
            padding: .6rem 1.25rem;
            border-radius: .5rem;
            margin: .1rem .75rem;
            transition: all .2s;
            font-size: .875rem;
            font-weight: 500;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, .15);
            color: white;
            transform: translateX(3px);
        }

        .sidebar .nav-link.active {
            background: var(--gold);
            color: white;
            box-shadow: 0 2px 8px rgba(197, 160, 40, .4);
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: .5rem;
        }

        /* === MAIN CONTENT === */
        .main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            transition: margin .3s ease;
        }

        /* === TOPBAR === */
        .topbar {
            background: white;
            padding: .75rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        /* === CARDS === */
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
        }

        .card-header {
            border-radius: 1rem 1rem 0 0 !important;
            border-bottom: 1px solid rgba(0, 0, 0, .05);
        }

        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.25rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .1);
        }

        .stat-card .icon-box {
            width: 52px;
            height: 52px;
            border-radius: .875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* === BADGE JENJANG === */
        .badge-tk {
            background: #fef3c7;
            color: #d97706;
            border: 1px solid #fbbf24;
        }

        .badge-sd {
            background: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #93c5fd;
        }

        .badge-smp {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #86efac;
        }

        /* === TABLE === */
        .table {
            font-size: .875rem;
        }

        .table thead th {
            background: #f8fafc;
            font-weight: 700;
            font-size: .75rem;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        /* === BUTTONS === */
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .bulan-btn {
            min-width: 60px;
            padding: .35rem .5rem;
            font-size: .8rem;
            border-radius: .5rem;
            transition: all .2s;
        }

        .bulan-btn.dibayar {
            background: #dcfce7;
            color: #16a34a;
            border-color: #86efac;
        }

        .bulan-btn.belum {
            background: #fff;
            color: #64748b;
            border-color: #cbd5e1;
        }

        .bulan-btn.selected {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .bulan-btn.tidak-aktif {
            background: #f1f5f9;
            color: #cbd5e1;
            border-color: #e2e8f0;
            cursor: not-allowed;
        }

        /* === RESPONSIVE === */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

    <!-- SIDEBAR -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <h6>{{ config('app.name', 'Pembayaran Sekolah') }}</h6>
            <small>Sistem Informasi Pembayaran</small>
        </div>

        <div class="py-2 sidebar-nav">
            <div class="nav-section-title">Menu Utama</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <div class="nav-section-title">Data Master</div>
            <a href="{{ route('siswa.index') }}" class="nav-link {{ request()->routeIs('siswa.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Data Siswa
            </a>

            <div class="nav-section-title">Transaksi</div>
            <a href="{{ route('pembayaran.index') }}"
                class="nav-link {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i> Pembayaran
            </a>
            <a href="{{ route('setoran.index') }}"
                class="nav-link {{ request()->routeIs('setoran.*') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i> Setoran
            </a>

            <div class="nav-section-title">Cetak & Laporan</div>
            <a href="{{ route('cetak.index') }}" class="nav-link {{ request()->routeIs('cetak.*') ? 'active' : '' }}">
                <i class="bi bi-printer"></i> Cetak Kartu
            </a>
            <a href="{{ route('laporan.index') }}"
                class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Laporan
            </a>

            @if (auth()->user()->isAdminYayasan())
                <div class="nav-section-title">Administrasi</div>
                <a href="{{ route('admin.users.index') }}"
                    class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-person-gear"></i> Kelola User
                </a>
            @endif

            <a href="{{ route('setting.index') }}" class="nav-link {{ request()->routeIs('setting.*') ? 'active' : '' }}">
                <i class="bi bi-gear me-2"></i>Pengaturan
            </a>
        </div>

        <!-- User info di bawah -->
        <div class="w-100 p-3 sidebar-footer" style="border-top:1px solid rgba(255,255,255,.15);flex-shrink:0">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center text-white fw-bold"
                    style="width:36px;height:36px;font-size:.9rem;flex-shrink:0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <div class="text-white fw-600" style="font-size:.8rem">{{ auth()->user()->name }}</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6)">{{ auth()->user()->role_label }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="ms-auto">
                    @csrf
                    <button type="submit" class="btn btn-sm text-white p-0" title="Logout">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm d-md-none" id="sidebarToggle">
                    <i class="bi bi-list fs-4 text-secondary"></i>
                </button>
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">
                                <i class="bi bi-house"></i>
                            </a>
                        </li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill" style="background:var(--primary);font-size:.75rem">
                    {{ auth()->user()->role_label }}
                </span>
                <span class="text-muted small d-none d-sm-inline">
                    {{ now()->isoFormat('dddd, D MMMM Y') }}
                </span>
            </div>
        </div>

        <!-- PAGE CONTENT -->
        <div class="p-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm"
                    role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger rounded-3 border-0 shadow-sm">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <strong>Perhatian:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        // Toggle sidebar mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Format currency
        function formatRupiah(num) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(num);
        }
    </script>

    @stack('scripts')
</body>

</html>
