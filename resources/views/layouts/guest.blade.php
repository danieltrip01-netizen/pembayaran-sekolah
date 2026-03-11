{{--
    resources/views/layouts/guest.blade.php
    ─────────────────────────────────────────
    Layout untuk halaman tamu (Login, Register, dll).
    Konsisten dengan design system Bootstrap EduPay.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'EduPay') }} — @yield('title', 'Masuk')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        /* ── Tokens (sama dengan app.blade.php) ── */
        :root {
            --navy:       #0C1E3E;
            --navy-mid:   #142952;
            --navy-soft:  #1C3566;
            --blue:       #2563EB;
            --blue-dark:  #1D4ED8;
            --blue-light: #BFDBFE;
            --blue-pale:  #EFF6FF;
            --ink:        #0F172A;
            --ink-soft:   #334155;
            --ink-muted:  #64748B;
            --ink-faint:  #94A3B8;
            --border:     #E2E8F0;
            --bg:         #F8FAFC;
            --surface:    #FFFFFF;
            --green:      #10B981;
            --r-xl:       14px;
            --r-lg:       10px;
            --r-md:       8px;
            --r-pill:     999px;
            --shadow-pop: 0 8px 32px rgba(0,0,0,.10);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--navy);
            color: var(--ink-soft);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;

            /* Subtle grid texture (mirrors sidebar) */
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* Top-right glow */
        body::before {
            content: '';
            position: fixed;
            top: -120px; right: -120px;
            width: 480px; height: 480px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37,99,235,.18) 0%, transparent 65%);
            pointer-events: none;
        }

        /* Bottom-left glow */
        body::after {
            content: '';
            position: fixed;
            bottom: -80px; left: -80px;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16,185,129,.1) 0%, transparent 65%);
            pointer-events: none;
        }

        /* ── Auth card ── */
        .auth-card {
            width: 100%;
            max-width: 420px;
            background: var(--surface);
            border-radius: var(--r-xl);
            box-shadow: var(--shadow-pop);
            overflow: hidden;
            position: relative;
            z-index: 1;
            animation: fadeUp .4s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Card header / brand strip */
        .auth-header {
            background: var(--navy);
            padding: 2rem 2rem 1.6rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .auth-header::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 140px; height: 140px;
            background: linear-gradient(225deg, rgba(37,99,235,.2) 0%, transparent 60%);
            pointer-events: none;
        }

        .auth-logo {
            width: 52px; height: 52px;
            background: var(--blue);
            border-radius: var(--r-lg);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #fff;
            margin-bottom: .9rem;
            position: relative;
            z-index: 1;
            box-shadow: 0 4px 14px rgba(37,99,235,.4);
        }

        .auth-app-name {
            font-family: 'Sora', sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: .2px;
            position: relative;
            z-index: 1;
        }

        .auth-tagline {
            font-size: .68rem;
            color: rgba(255,255,255,.42);
            letter-spacing: .5px;
            margin-top: .2rem;
            position: relative;
            z-index: 1;
        }

        /* Card body */
        .auth-body {
            padding: 1.9rem 2rem 2rem;
        }

        .auth-title {
            font-family: 'Sora', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: .25rem;
        }

        .auth-subtitle {
            font-size: .8rem;
            color: var(--ink-muted);
            margin-bottom: 1.6rem;
        }

        /* Form overrides */
        .form-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--ink-soft);
            margin-bottom: .35rem;
        }

        .form-control {
            font-family: 'DM Sans', sans-serif;
            border: 1.5px solid var(--border);
            border-radius: var(--r-md);
            font-size: .875rem;
            padding: .62rem .9rem;
            color: var(--ink);
            background: var(--surface);
            transition: border-color .18s, box-shadow .18s;
        }

        .form-control::placeholder { color: var(--ink-faint); }

        .form-control:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
            outline: none;
        }

        .input-group-text {
            background: var(--bg);
            border: 1.5px solid var(--border);
            color: var(--ink-muted);
            font-size: .9rem;
        }

        /* Submit button */
        .btn-auth {
            width: 100%;
            padding: .72rem;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            font-weight: 700;
            border-radius: var(--r-md);
            background: var(--navy);
            border: none;
            color: #fff;
            cursor: pointer;
            transition: background .18s, transform .18s, box-shadow .18s;
            letter-spacing: .2px;
        }

        .btn-auth:hover {
            background: var(--navy-soft);
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(12,30,62,.25);
        }

        .btn-auth:active { transform: none; }

        /* Checkbox */
        .form-check-input:checked {
            background-color: var(--navy);
            border-color: var(--navy);
        }

        .form-check-label {
            font-size: .8rem;
            color: var(--ink-muted);
        }

        /* Forgot / links */
        .auth-link {
            font-size: .8rem;
            color: var(--blue);
            text-decoration: none;
            font-weight: 500;
            transition: color .15s;
        }

        .auth-link:hover { color: var(--blue-dark); text-decoration: underline; }

        /* Alerts */
        .alert {
            border: none;
            border-radius: var(--r-lg);
            font-size: .845rem;
            font-weight: 500;
            padding: .8rem 1rem;
            display: flex;
            align-items: flex-start;
            gap: .5rem;
        }

        .alert-danger  { background: #FEF2F2; color: #991B1B; border-left: 3px solid #EF4444; }
        .alert-success { background: #D1FAE5; color: #065F46; border-left: 3px solid #10B981; }

        /* Divider */
        .auth-divider {
            position: relative;
            text-align: center;
            margin: 1.2rem 0;
        }

        .auth-divider::before {
            content: '';
            position: absolute;
            top: 50%; left: 0; right: 0;
            height: 1px;
            background: var(--border);
        }

        .auth-divider span {
            position: relative;
            background: var(--surface);
            padding: 0 .75rem;
            font-size: .73rem;
            color: var(--ink-faint);
            font-weight: 500;
        }

        /* Footer note */
        .auth-footer-note {
            text-align: center;
            font-size: .72rem;
            color: rgba(255,255,255,.3);
            margin-top: 1.4rem;
            position: relative;
            z-index: 1;
        }
    </style>

    @stack('styles')
</head>
<body>

    <div>
        {{-- ── Auth card ── --}}
        <div class="auth-card">

            {{-- Brand header --}}
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="bi bi-mortarboard-fill" aria-hidden="true"></i>
                </div>
                <div class="auth-app-name">{{ config('app.name', 'EduPay') }}</div>
                <div class="auth-tagline">Sistem Pembayaran Sekolah</div>
            </div>

            {{-- Page content (login form, register form, etc.) --}}
            <div class="auth-body">

                {{-- Session alerts --}}
                @if (session('status'))
                <div class="alert alert-success mb-3" role="alert">
                    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                    <span>{{ session('status') }}</span>
                </div>
                @endif

                @if ($errors->any())
                <div class="alert alert-danger mb-3" role="alert">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{ $slot }}

            </div>
        </div>

        {{-- Subtle copyright note --}}
        <p class="auth-footer-note">
            &copy; {{ date('Y') }} {{ config('app.name', 'EduPay') }} &mdash; Hak cipta dilindungi
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle show/hide password untuk semua tombol .pw-toggle
        document.querySelectorAll('.pw-toggle').forEach(btn => {
            btn.addEventListener('click', function () {
                const input = document.getElementById(this.dataset.target);
                if (!input) return;
                const show = input.type === 'password';
                input.type  = show ? 'text' : 'password';
                this.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        });
    </script>

    @stack('scripts')
</body>
</html>