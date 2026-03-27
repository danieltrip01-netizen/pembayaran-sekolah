<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DKas') }} — @yield('title', 'Dashboard')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ============================================================
           TOKENS
        ============================================================ */
        :root {
            --navy:         #0C1E3E;
            --navy-mid:     #142952;
            --navy-soft:    #1C3566;
            --blue:         #2563EB;
            --blue-dark:    #1D4ED8;
            --blue-mid:     #3B82F6;
            --blue-light:   #BFDBFE;
            --blue-pale:    #EFF6FF;

            --green:        #10B981;
            --green-pale:   #D1FAE5;
            --red:          #EF4444;
            --red-pale:     #FEF2F2;
            --yellow:       #F59E0B;
            --yellow-pale:  #FFFBEB;
            --orange:       #F97316;
            --orange-pale:  #FFF7ED;

            --ink:          #0F172A;
            --ink-soft:     #334155;
            --ink-muted:    #64748B;
            --ink-faint:    #94A3B8;
            --border:       #E2E8F0;
            --bg:           #F8FAFC;
            --surface:      #FFFFFF;

            /* Alias — dipakai di seluruh halaman child */
            --primary:      #0C1E3E;

            --sidebar-w:    256px;
            --topbar-h:     60px;
            --r-xl:         14px;
            --r-lg:         10px;
            --r-md:         8px;
            --r-sm:         5px;
            --r-pill:       999px;

            --shadow-card:  0 1px 3px rgba(0,0,0,.04), 0 4px 12px rgba(0,0,0,.04);
            --shadow-pop:   0 8px 24px rgba(0,0,0,.10);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--ink-soft);
            min-height: 100vh;
        }

        /* ============================================================
           UTILITIES
        ============================================================ */
        .fw-600 { font-weight: 600 !important; }

        /* Soft color backgrounds (Bootstrap supplement) */
        .bg-soft-primary   { background-color: #e0e7ff !important; }
        .bg-soft-success   { background-color: #d1fae5 !important; }
        .bg-soft-danger    { background-color: #fee2e2 !important; }
        .bg-soft-warning   { background-color: #fef9c3 !important; }
        .bg-soft-secondary { background-color: #f1f5f9 !important; }
        .bg-soft-info      { background-color: #e0f2fe !important; }

        /* Jenjang badges */
        .badge-tk, .badge-TK {
            display: inline-flex; align-items: center;
            font-size: .68rem; font-weight: 600;
            padding: .25rem .65rem; border-radius: var(--r-pill);
            background: var(--yellow-pale); color: #B45309;
            border: 1px solid #FDE68A;
        }
        .badge-sd, .badge-SD {
            display: inline-flex; align-items: center;
            font-size: .68rem; font-weight: 600;
            padding: .25rem .65rem; border-radius: var(--r-pill);
            background: var(--blue-pale); color: var(--blue-dark);
            border: 1px solid var(--blue-light);
        }
        .badge-smp, .badge-SMP {
            display: inline-flex; align-items: center;
            font-size: .68rem; font-weight: 600;
            padding: .25rem .65rem; border-radius: var(--r-pill);
            background: var(--green-pale); color: #065F46;
            border: 1px solid #6EE7B7;
        }

        /* ============================================================
           SIDEBAR
        ============================================================ */
        .sidebar {
            width: var(--sidebar-w);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            background: var(--navy);
            overflow: hidden;
            transition: transform .3s cubic-bezier(.4,0,.2,1);
        }

        /* Subtle gradient accent top-right */
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 200px; height: 200px;
            background: radial-gradient(ellipse at top right, rgba(37,99,235,.14) 0%, transparent 65%);
            pointer-events: none;
        }

        /* Sidebar hidden state (desktop & mobile) */
        body.sidebar-hidden .sidebar {
            transform: translateX(-100%);
        }

        body.sidebar-hidden .main-content {
            margin-left: 0;
        }

        /* Brand */
        .sidebar-brand {
            position: relative;
            z-index: 2;
            padding: 1.2rem 1.1rem 1.1rem;
            border-bottom: 1px solid rgba(255,255,255,.07);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: .8rem;
        }

        .brand-logo {
            width: 38px; height: 38px;
            background: var(--blue);
            border-radius: var(--r-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #fff;
            flex-shrink: 0;
        }

        .brand-text { line-height: 1; }
        .brand-name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: .92rem;
            font-weight: 600;
            color: #fff;
            letter-spacing: .2px;
        }
        .brand-tagline {
            font-size: .62rem;
            color: rgba(255,255,255,.42);
            font-weight: 400;
            letter-spacing: .3px;
            margin-top: .18rem;
        }

        /* Nav */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: .65rem 0 .5rem;
            position: relative;
            z-index: 2;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.06) transparent;
        }

        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.08); border-radius: 4px; }

        .nav-section-label {
            font-size: .57rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(255,255,255,.32);
            padding: .85rem 1.25rem .25rem;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: .65rem;
            color: rgba(255,255,255,.62);
            font-size: .84rem;
            font-weight: 500;
            padding: .52rem .85rem .52rem 1.1rem;
            margin: .05rem .65rem;
            border-radius: var(--r-md);
            transition: all .16s ease;
            position: relative;
            text-decoration: none;
        }

        .sidebar .nav-link:hover {
            background: rgba(255,255,255,.09);
            color: rgba(255,255,255,.95);
        }

        .sidebar .nav-link.active {
            background: rgba(37,99,235,.28);
            color: #fff;
            font-weight: 600;
        }

        /* Left accent bar for active */
        .sidebar .nav-link.active::before {
            content: '';
            position: absolute;
            left: -3px;
            top: 20%; bottom: 20%;
            width: 3px;
            border-radius: 0 3px 3px 0;
            background: var(--blue-mid);
        }

        .nav-icon-wrap {
            width: 28px; height: 28px;
            border-radius: var(--r-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .88rem;
            flex-shrink: 0;
            background: rgba(255,255,255,.07);
            transition: background .16s;
        }

        .sidebar .nav-link:hover .nav-icon-wrap {
            background: rgba(255,255,255,.12);
        }

        .sidebar .nav-link.active .nav-icon-wrap {
            background: var(--blue);
            color: #fff;
        }

        /* Sidebar footer */
        .sidebar-footer {
            position: relative;
            z-index: 2;
            flex-shrink: 0;
            padding: .85rem 1rem;
            border-top: 1px solid rgba(255,255,255,.06);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: .65rem;
        }

        .user-avatar {
            width: 34px; height: 34px;
            border-radius: var(--r-md);
            background: var(--navy-soft);
            border: 1.5px solid rgba(255,255,255,.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: .88rem;
            font-weight: 600;
            color: #fff;
            flex-shrink: 0;
        }

        .user-details { flex: 1; min-width: 0; }
        .user-name {
            font-size: .78rem;
            font-weight: 600;
            color: rgba(255,255,255,.9);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-role-tag {
            font-size: .62rem;
            color: rgba(255,255,255,.42);
            margin-top: .1rem;
        }

        .btn-logout {
            width: 30px; height: 30px;
            border-radius: var(--r-sm);
            border: 1px solid rgba(255,255,255,.1);
            background: transparent;
            color: rgba(255,255,255,.42);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: all .18s;
            flex-shrink: 0;
            font-size: .85rem;
        }
        .btn-logout:hover {
            background: rgba(239,68,68,.18);
            border-color: rgba(239,68,68,.35);
            color: #FCA5A5;
        }

        /* ============================================================
           MAIN CONTENT
        ============================================================ */
        .main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin .3s cubic-bezier(.4,0,.2,1);
        }

        /* ============================================================
           TOPBAR
        ============================================================ */
        .topbar {
            height: var(--topbar-h);
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.75rem;
            flex-shrink: 0;
            box-shadow: 0 1px 0 var(--border);
        }

        .topbar-left { display: flex; align-items: center; gap: .9rem; }
        .topbar-right { display: flex; align-items: center; gap: .65rem; }

        .breadcrumb { margin: 0; font-size: .78rem; }
        .breadcrumb-item a {
            color: var(--ink-muted);
            text-decoration: none;
            transition: color .15s;
            font-weight: 500;
        }
        .breadcrumb-item a:hover { color: var(--blue); }
        .breadcrumb-item.active {
            color: var(--ink);
            font-weight: 600;
        }
        .breadcrumb-item + .breadcrumb-item::before {
            color: var(--ink-faint);
            content: '›';
        }

        .topbar-date-pill {
            font-size: .72rem;
            font-weight: 500;
            color: var(--ink-muted);
            background: var(--bg);
            border: 1px solid var(--border);
            padding: .28rem .8rem;
            border-radius: var(--r-pill);
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .7rem;
            font-weight: 600;
            padding: .3rem .75rem;
            border-radius: var(--r-pill);
            background: var(--blue-pale);
            color: var(--blue-dark);
            border: 1px solid var(--blue-light);
            letter-spacing: .2px;
        }

        .role-badge .live-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--green);
        }

        /* Toggle button — visible on ALL screen sizes */
        .btn-toggle-sidebar {
            display: flex;
            width: 34px; height: 34px;
            border-radius: var(--r-md);
            border: 1px solid var(--border);
            background: transparent;
            align-items: center;
            justify-content: center;
            color: var(--ink-muted);
            cursor: pointer;
            transition: all .18s;
            flex-shrink: 0;
        }

        .btn-toggle-sidebar:hover {
            background: var(--bg);
            color: var(--blue);
            border-color: var(--blue-light);
        }

        /* ============================================================
           PAGE BODY
        ============================================================ */
        .page-body {
            flex: 1;
            padding: 1.75rem 2rem;
        }

        /* ============================================================
           ALERTS
        ============================================================ */
        .alert {
            border: none;
            border-radius: var(--r-lg);
            font-size: .865rem;
            font-weight: 500;
            padding: .85rem 1.1rem;
            display: flex;
            align-items: flex-start;
            gap: .55rem;
            animation: slideDown .3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .alert-success { background: var(--green-pale); color: #065F46; border-left: 3px solid var(--green); }
        .alert-danger  { background: var(--red-pale);   color: #991B1B; border-left: 3px solid var(--red); }
        .alert-warning { background: var(--yellow-pale); color: #92400E; border-left: 3px solid var(--yellow); }

        .alert .btn-close { margin-left: auto; flex-shrink: 0; }

        /* ============================================================
           CARDS
        ============================================================ */
        .card {
            border: 1px solid var(--border);
            border-radius: var(--r-xl);
            box-shadow: var(--shadow-card);
            background: var(--surface);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            border-radius: var(--r-xl) var(--r-xl) 0 0 !important;
            padding: 1rem 1.4rem;
            font-size: .88rem;
            font-weight: 700;
            color: var(--ink);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .card-body { padding: 1.4rem; }

        /* Stat cards */
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r-xl);
            padding: 1.4rem 1.5rem;
            box-shadow: var(--shadow-card);
            transition: transform .2s ease, box-shadow .2s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-pop);
        }

        .stat-card .stat-accent {
            position: absolute;
            top: 0; left: 1.2rem; right: 1.2rem;
            height: 2px;
            border-radius: 0 0 4px 4px;
            opacity: .6;
        }

        .stat-label {
            font-size: .67rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--ink-muted);
            margin-bottom: .35rem;
        }

        .stat-value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.15;
        }

        .stat-sub {
            font-size: .74rem;
            color: var(--ink-faint);
            margin-top: .2rem;
        }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: var(--r-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        /* ============================================================
           JENJANG BADGE (alias jenjang-badge for old code)
        ============================================================ */
        .jenjang-badge {
            font-size: .68rem;
            font-weight: 600;
            padding: .25rem .65rem;
            border-radius: var(--r-pill);
            letter-spacing: .3px;
        }

        .jenjang-tk  { background: var(--yellow-pale); color: #B45309; border: 1px solid #FDE68A; }
        .jenjang-sd  { background: var(--blue-pale);   color: var(--blue-dark); border: 1px solid var(--blue-light); }
        .jenjang-smp { background: var(--green-pale);  color: #065F46; border: 1px solid #6EE7B7; }

        /* ============================================================
           TABLE
        ============================================================ */
        .table {
            font-size: .86rem;
            color: var(--ink-soft);
            margin-bottom: 0;
        }

        .table thead th {
            font-size: .67rem;
            font-weight: 700;
            letter-spacing: .9px;
            text-transform: uppercase;
            color: var(--ink-muted);
            background: var(--bg);
            border-bottom: 1.5px solid var(--border);
            padding: .85rem 1rem;
            white-space: nowrap;
        }

        .table tbody td {
            padding: .85rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #F1F5F9;
            color: var(--ink-soft);
        }

        .table tbody tr:last-child td { border-bottom: none; }

        .table tbody tr {
            transition: background .12s;
        }

        .table tbody tr:hover td { background: var(--blue-pale); }

        /* ============================================================
           BUTTONS
        ============================================================ */
        .btn {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 600;
            border-radius: var(--r-md);
            transition: all .18s ease;
            font-size: .855rem;
        }

        .btn-primary {
            background: var(--navy);
            border-color: var(--navy);
            color: #fff;
        }

        .btn-primary:hover, .btn-primary:focus {
            background: var(--navy-soft);
            border-color: var(--navy-soft);
            box-shadow: 0 4px 12px rgba(12,30,62,.2);
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-outline-primary {
            border-color: var(--blue-light);
            color: var(--blue);
        }

        .btn-outline-primary:hover {
            background: var(--blue-pale);
            border-color: var(--blue);
            color: var(--blue-dark);
        }

        .btn-warning {
            background: var(--yellow);
            border-color: var(--yellow);
            color: #fff;
            font-weight: 600;
        }

        .btn-warning:hover {
            background: #D97706;
            border-color: #D97706;
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-outline-secondary {
            border-color: var(--border);
            color: var(--ink-muted);
        }

        .btn-outline-secondary:hover {
            background: var(--bg);
            border-color: var(--blue-light);
            color: var(--blue);
        }

        .btn-outline-warning {
            border-color: #FCD34D;
            color: #B45309;
        }

        .btn-outline-warning:hover {
            background: var(--yellow-pale);
            border-color: var(--yellow);
            color: #92400E;
        }

        .btn-outline-success {
            border-color: #6EE7B7;
            color: #065F46;
        }

        .btn-outline-success:hover {
            background: var(--green-pale);
            border-color: var(--green);
            color: #065F46;
        }

        .btn-outline-danger {
            border-color: #FECACA;
            color: #DC2626;
        }

        .btn-outline-danger:hover {
            background: var(--red-pale);
            border-color: var(--red);
            color: #991B1B;
        }

        /* ============================================================
           BULAN BUTTONS
        ============================================================ */
        .bulan-btn {
            min-width: 56px;
            padding: .35rem .45rem;
            font-size: .76rem;
            font-weight: 600;
            border-radius: var(--r-sm);
            transition: all .14s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .bulan-btn.dibayar {
            background: var(--green-pale);
            color: #065F46;
            border: 1px solid #6EE7B7;
        }

        .bulan-btn.belum {
            background: var(--surface);
            color: var(--ink-muted);
            border: 1px solid var(--border);
        }

        .bulan-btn.belum:hover { background: var(--blue-pale); border-color: var(--blue-light); color: var(--blue); }

        .bulan-btn.selected {
            background: var(--navy);
            color: #fff;
            border: 1px solid var(--navy);
        }

        .bulan-btn.tidak-aktif {
            background: var(--bg);
            color: var(--border);
            border: 1px solid var(--border);
            cursor: not-allowed;
            opacity: .5;
        }

        /* ============================================================
           FORM
        ============================================================ */
        .form-label {
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--ink-soft);
            margin-bottom: .4rem;
        }

        .form-control, .form-select {
            font-family: 'Plus Jakarta Sans', sans-serif;
            border: 1.5px solid var(--border);
            border-radius: var(--r-md);
            font-size: .875rem;
            padding: .6rem .875rem;
            color: var(--ink);
            background: var(--surface);
            transition: border-color .18s, box-shadow .18s;
        }

        .form-control::placeholder { color: var(--ink-faint); }

        .form-control:focus, .form-select:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
            outline: none;
            color: var(--ink);
        }

        .form-text {
            font-size: .74rem;
            color: var(--ink-muted);
            margin-top: .3rem;
        }

        .input-group-text {
            font-size: .875rem;
            color: var(--ink-muted);
            background: var(--bg);
            border: 1.5px solid var(--border);
        }

        /* ============================================================
           PAGINATION
        ============================================================ */
        .pagination { gap: .2rem; }

        .page-link {
            border-radius: var(--r-md) !important;
            border: 1px solid var(--border);
            color: var(--ink-muted);
            font-size: .82rem;
            font-weight: 600;
            padding: .42rem .72rem;
            transition: all .14s;
        }

        .page-link:hover { background: var(--blue-pale); border-color: var(--blue-light); color: var(--blue); }
        .page-item.active .page-link { background: var(--navy); border-color: var(--navy); color: #fff; }

        /* ============================================================
           MODAL
        ============================================================ */
        .modal-content { border: none; border-radius: var(--r-xl); box-shadow: var(--shadow-pop); }
        .modal-header { border-bottom: 1px solid var(--border); padding: 1.2rem 1.5rem; border-radius: var(--r-xl) var(--r-xl) 0 0; }
        .modal-title { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1rem; font-weight: 700; color: var(--ink); }
        .modal-footer { border-top: 1px solid var(--border); padding: 1rem 1.5rem; }

        /* ============================================================
           RESPONSIVE
        ============================================================ */
        @media (max-width: 768px) {
            /* On mobile, sidebar is off-canvas by default */
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            /* Override hidden state on mobile — show class wins */
            body.sidebar-hidden .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
            }
            .page-body { padding: 1.25rem 1rem; }
            .topbar { padding: 0 1rem; }
        }

        /* Sidebar overlay backdrop (mobile) */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 1039;
        }

        .sidebar-backdrop.show { display: block; }
    </style>

    @stack('styles')
</head>
<body>

{{-- ── Sidebar (backdrop + nav) ─────────────────────────────── --}}
@include('layouts.navigation')

<!-- ================================================================
     MAIN CONTENT
================================================================ -->
<div class="main-content">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-left">
            <!-- Toggle button — visible on desktop AND mobile -->
            <button class="btn-toggle-sidebar" id="sidebarToggle" title="Sembunyikan/tampilkan sidebar">
                <i class="bi bi-list" style="font-size:1.1rem"></i>
            </button>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">
                            <i class="bi bi-house" style="font-size:.8rem"></i>
                        </a>
                    </li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>

        <div class="topbar-right">
            <span class="topbar-date-pill d-none d-sm-inline">
                <i class="bi bi-calendar3 me-1" style="font-size:.7rem"></i>
                {{ now()->isoFormat('dddd, D MMMM Y') }}
            </span>
            <span class="role-badge">
                <span class="live-dot"></span>
                {{ auth()->user()->role_label }}
            </span>
        </div>
    </header>

    <!-- PAGE BODY -->
    <main class="page-body">

        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
            <div>
                <strong>Perhatian:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    const sidebar         = document.getElementById('sidebar');
    const sidebarToggle   = document.getElementById('sidebarToggle');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    // Restore sidebar state from localStorage
    const SIDEBAR_KEY = 'edupay_sidebar_hidden';
    if (localStorage.getItem(SIDEBAR_KEY) === '1' && window.innerWidth > 768) {
        document.body.classList.add('sidebar-hidden');
    }

    function isMobile() { return window.innerWidth <= 768; }

    sidebarToggle?.addEventListener('click', () => {
        if (isMobile()) {
            // Mobile: toggle show class + backdrop
            const open = sidebar.classList.toggle('show');
            sidebarBackdrop.classList.toggle('show', open);
        } else {
            // Desktop: toggle body class + persist
            const hidden = document.body.classList.toggle('sidebar-hidden');
            localStorage.setItem(SIDEBAR_KEY, hidden ? '1' : '0');
        }
    });

    // Close sidebar on backdrop click (mobile)
    sidebarBackdrop.addEventListener('click', () => {
        sidebar.classList.remove('show');
        sidebarBackdrop.classList.remove('show');
    });

    // Auto-dismiss alerts after 5s
    setTimeout(() => {
        document.querySelectorAll('.alert.fade.show').forEach(el => {
            bootstrap.Alert.getOrCreateInstance(el)?.close();
        });
    }, 5000);

    // Rupiah formatter
    function formatRupiah(n) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0
        }).format(n);
    }
</script>

@stack('scripts')
</body>
</html>