{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — {{ config('app.name', 'DKas') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --navy: #0C1E3E;
            --blue: #2563EB;
            --blue-focus: rgba(37, 99, 235, .12);
            --ink: #111827;
            --ink-mid: #374151;
            --ink-muted: #6B7280;
            --border: #E5E7EB;
            --border-focus: #2563EB;
            --surface: #FFFFFF;
            --bg: #F5F7FA;
            --red: #EF4444;
            --red-pale: #FEF2F2;
            --green: #10B981;
            --green-pale: #D1FAE5;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        /* ── Wrapper ── */
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            animation: fadeUp .5s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Brand ── */
        .brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 2.25rem;
            justify-content: center;
        }

        .brand-mark {
            width: 38px;
            height: 38px;
            background: var(--navy);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .brand-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -.2px;
        }

        /* ── Card ── */
        .login-card {
            background: var(--surface);
            border-radius: 16px;
            padding: 2.5rem 2.25rem;
            box-shadow:
                0 0 0 1px rgba(0, 0, 0, .05),
                0 4px 6px -1px rgba(0, 0, 0, .04),
                0 16px 32px -8px rgba(0, 0, 0, .08);
        }

        /* ── Heading ── */
        .card-heading {
            margin-bottom: 1.75rem;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -.4px;
            line-height: 1.2;
            margin-bottom: .35rem;
        }

        .card-subtitle {
            font-size: .875rem;
            color: var(--ink-muted);
            font-weight: 400;
        }

        /* ── Alerts ── */
        .alert-msg {
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            padding: .85rem 1rem;
            border-radius: 10px;
            font-size: .84rem;
            font-weight: 500;
            margin-bottom: 1.25rem;
            line-height: 1.5;
        }

        .alert-msg i {
            flex-shrink: 0;
            margin-top: .1rem;
            font-size: .9rem;
        }

        .alert-danger {
            background: var(--red-pale);
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        .alert-success {
            background: var(--green-pale);
            color: #065F46;
            border: 1px solid #6EE7B7;
        }

        /* ── Fields ── */
        .field {
            margin-bottom: 1.1rem;
        }

        .field-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: var(--ink-mid);
            margin-bottom: .45rem;
            letter-spacing: .1px;
        }

        .field-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            left: .9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--ink-muted);
            font-size: .85rem;
            pointer-events: none;
            transition: color .15s;
        }

        .field-wrap:focus-within .field-icon {
            color: var(--blue);
        }

        .field-input {
            width: 100%;
            padding: .75rem .9rem .75rem 2.45rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: .9rem;
            color: var(--ink);
            outline: none;
            transition: border-color .18s, box-shadow .18s;
            -webkit-appearance: none;
        }

        .field-input::placeholder {
            color: #D1D5DB;
            font-weight: 300;
        }

        .field-input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3.5px var(--blue-focus);
        }

        /* Password toggle */
        .pw-toggle {
            position: absolute;
            right: .85rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--ink-muted);
            cursor: pointer;
            font-size: .85rem;
            padding: 0;
            line-height: 1;
            transition: color .15s;
        }

        .pw-toggle:hover {
            color: var(--blue);
        }

        /* ── Extras ── */
        .extras {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: .5rem 0 1.6rem;
        }

        .check-wrap {
            display: flex;
            align-items: center;
            gap: .45rem;
            cursor: pointer;
        }

        .check-wrap input[type="checkbox"] {
            width: 15px;
            height: 15px;
            border-radius: 4px;
            accent-color: var(--blue);
            cursor: pointer;
        }

        .check-wrap span {
            font-size: .83rem;
            color: var(--ink-muted);
            font-weight: 400;
        }

        .link-forgot {
            font-size: .83rem;
            font-weight: 600;
            color: var(--blue);
            text-decoration: none;
        }

        .link-forgot:hover {
            text-decoration: underline;
        }

        /* ── Submit ── */
        .btn-login {
            width: 100%;
            padding: .875rem;
            background: var(--navy);
            border: none;
            border-radius: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: .92rem;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            letter-spacing: .1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            transition: background .2s, transform .15s, box-shadow .2s;
        }

        .btn-login:hover {
            background: #142952;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(12, 30, 62, .22);
        }

        .btn-login:active {
            transform: translateY(0);
            box-shadow: none;
        }

        /* ── Footer ── */
        .card-footer-note {
            text-align: center;
            margin-top: 1.5rem;
            font-size: .78rem;
            color: var(--ink-muted);
        }

        .card-footer-note a {
            color: var(--blue);
            font-weight: 600;
            text-decoration: none;
        }

        .card-footer-note a:hover {
            text-decoration: underline;
        }

        /* ── Copyright ── */
        .copyright {
            text-align: center;
            margin-top: 1.75rem;
            font-size: .72rem;
            color: #9CA3AF;
        }

        /* ── Divider ── */
        .divider {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin: 1.25rem 0;
            color: var(--ink-muted);
            font-size: .75rem;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── Register button ── */
        .btn-register {
            width: 100%;
            padding: .875rem;
            background: transparent;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: .92rem;
            font-weight: 600;
            color: var(--ink-mid);
            cursor: pointer;
            letter-spacing: .1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            text-decoration: none;
            transition: border-color .2s, color .2s, background .2s, transform .15s;
        }

        .btn-register:hover {
            border-color: var(--blue);
            color: var(--blue);
            background: var(--blue-focus);
            transform: translateY(-1px);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
                border-radius: 14px;
            }

            .card-title {
                font-size: 1.35rem;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">

        <!-- Card -->
        <div class="login-card">

            <!-- Brand -->
            <div class="brand">
                <img src="img/logo-dpay.png" alt="Logo" style="height: 60px; width: auto;">
            </div>


            <div class="card-heading">
                <div class="card-title">Selamat datang</div>
                <div class="card-subtitle">Masukkan kredensial Anda untuk melanjutkan</div>
            </div>

            {{-- Error / Status --}}
            @if ($errors->any())
                <div class="alert-msg alert-danger">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (session('status'))
                <div class="alert-msg alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="field">
                    <label class="field-label" for="email">Email</label>
                    <div class="field-wrap">
                        <i class="bi bi-envelope field-icon"></i>
                        <input type="email" id="email" name="email" class="field-input"
                            value="{{ old('email') }}" placeholder="nama@sekolah.com" autocomplete="email" required
                            autofocus>
                    </div>
                </div>

                <!-- Password -->
                <div class="field">
                    <label class="field-label" for="password">Kata Sandi</label>
                    <div class="field-wrap">
                        <i class="bi bi-lock field-icon"></i>
                        <input type="password" id="password" name="password" class="field-input"
                            placeholder="Masukkan kata sandi" autocomplete="current-password" required>
                        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Tampilkan kata sandi">
                            <i class="bi bi-eye" id="pwIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Extras -->
                <div class="extras">
                    <label class="check-wrap">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Ingat saya</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="link-forgot">Lupa password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Masuk ke Sistem
                </button>
            </form>

            {{-- Divider --}}
            @if (Route::has('register'))
            <div class="divider">atau</div>

            <a href="{{ route('register') }}" class="btn-register">
                <i class="bi bi-person-plus"></i>
                Buat Akun Baru
            </a>
            @endif

        </div>

        <!-- Footer -->

        <div class="copyright">
            &copy; {{ date('Y') }} {{ config('app.name') }} &nbsp;·&nbsp; By Daniel Tri Pamungkas
        </div>

    </div>

    <script>
        const pwToggle = document.getElementById('pwToggle');
        const pwInput = document.getElementById('password');
        const pwIcon = document.getElementById('pwIcon');

        pwToggle?.addEventListener('click', function() {
            const show = pwInput.type === 'password';
            pwInput.type = show ? 'text' : 'password';
            pwIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>

</body>

</html>