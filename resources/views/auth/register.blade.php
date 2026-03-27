{{-- resources/views/auth/register.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — {{ config('app.name', 'DKas') }}</title>

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
        .register-wrapper {
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

        /* ── Card ── */
        .register-card {
            background: var(--surface);
            border-radius: 16px;
            padding: 2.5rem 2.25rem;
            box-shadow:
                0 0 0 1px rgba(0, 0, 0, .05),
                0 4px 6px -1px rgba(0, 0, 0, .04),
                0 16px 32px -8px rgba(0, 0, 0, .08);
        }

        /* ── Brand ── */
        .brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 2.25rem;
            justify-content: center;
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
            appearance: none;
        }

        .field-input::placeholder {
            color: #D1D5DB;
            font-weight: 300;
        }

        .field-input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3.5px var(--blue-focus);
        }

        /* Select khusus */
        select.field-input {
            padding-right: 2.5rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236B7280' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right .9rem center;
            cursor: pointer;
        }

        /* is-invalid state */
        .field-input.is-invalid {
            border-color: var(--red);
            box-shadow: 0 0 0 3.5px rgba(239, 68, 68, .1);
        }

        .invalid-feedback {
            display: block;
            font-size: .76rem;
            color: var(--red);
            margin-top: .35rem;
            font-weight: 500;
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

        /* Password field: beri ruang untuk toggle */
        .has-toggle .field-input {
            padding-right: 2.75rem;
        }

        /* ── Info hint ── */
        .field-hint {
            font-size: .74rem;
            color: var(--ink-muted);
            margin-top: .4rem;
            display: flex;
            align-items: flex-start;
            gap: .3rem;
            line-height: 1.45;
        }

        .field-hint i {
            flex-shrink: 0;
            margin-top: .1rem;
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
            margin-top: 1.6rem;
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

        /* ── Back to login button ── */
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

        .copyright {
            text-align: center;
            margin-top: 1.75rem;
            font-size: .72rem;
            color: #9CA3AF;
        }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .register-card {
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

    <div class="register-wrapper">

        <div class="register-card">

            <!-- Brand / Logo -->
            <div class="brand">
                <img src="../img/logo-dpay.png" alt="Logo" style="height: 60px; width: auto;">
            </div>

            <div class="card-heading">
                <div class="card-title">Buat Akun Baru</div>
                <div class="card-subtitle">Isi data di bawah untuk mendaftarkan akun Anda</div>
            </div>

            {{-- Error --}}
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

            <form method="POST" action="{{ route('register') }}">
                @csrf

                {{-- Nama Lengkap --}}
                <div class="field">
                    <label class="field-label" for="name">Nama Lengkap</label>
                    <div class="field-wrap">
                        <i class="bi bi-person field-icon"></i>
                        <input type="text"
                               id="name"
                               name="name"
                               class="field-input @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="Nama lengkap Anda"
                               autocomplete="name"
                               required autofocus>
                    </div>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="field">
                    <label class="field-label" for="email">Alamat Email</label>
                    <div class="field-wrap">
                        <i class="bi bi-envelope field-icon"></i>
                        <input type="email"
                               id="email"
                               name="email"
                               class="field-input @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="nama@sekolah.com"
                               autocomplete="username"
                               required>
                    </div>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Role / Jenjang --}}
                <div class="field">
                    <label class="field-label" for="role">Role / Jenjang</label>
                    <div class="field-wrap">
                        <i class="bi bi-building field-icon"></i>
                        <select id="role"
                                name="role"
                                class="field-input @error('role') is-invalid @enderror"
                                required>
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>-- Pilih Role Anda --</option>
                            <option value="admin_tk"  {{ old('role') == 'admin_tk'  ? 'selected' : '' }}>Admin TK (Taman Kanak-Kanak)</option>
                            <option value="admin_sd"  {{ old('role') == 'admin_sd'  ? 'selected' : '' }}>Admin SD (Sekolah Dasar)</option>
                            <option value="admin_smp" {{ old('role') == 'admin_smp' ? 'selected' : '' }}>Admin SMP (Sekolah Menengah Pertama)</option>
                        </select>
                    </div>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="field">
                    <label class="field-label" for="password">Kata Sandi</label>
                    <div class="field-wrap has-toggle">
                        <i class="bi bi-lock field-icon"></i>
                        <input type="password"
                               id="password"
                               name="password"
                               class="field-input @error('password') is-invalid @enderror"
                               placeholder="Minimal 8 karakter"
                               autocomplete="new-password"
                               required>
                        <button type="button" class="pw-toggle" data-target="password" aria-label="Tampilkan kata sandi">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div class="field">
                    <label class="field-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                    <div class="field-wrap has-toggle">
                        <i class="bi bi-lock-fill field-icon"></i>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               class="field-input @error('password_confirmation') is-invalid @enderror"
                               placeholder="Ulangi kata sandi"
                               autocomplete="new-password"
                               required>
                        <button type="button" class="pw-toggle" data-target="password_confirmation" aria-label="Tampilkan konfirmasi">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-person-plus"></i>
                    Buat Akun
                </button>
            </form>

            <div class="divider">atau</div>

            <a href="{{ route('login') }}" class="btn-register">
                <i class="bi bi-box-arrow-in-right"></i>
                Sudah punya akun? Masuk di sini
            </a>

        </div>


        <div class="copyright">
            &copy; {{ date('Y') }} {{ config('app.name') }} &nbsp;·&nbsp; Pusat Layanan IT
        </div>

    </div>

    <script>
        // Password toggle — berlaku untuk semua tombol pw-toggle
        document.querySelectorAll('.pw-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const input    = document.getElementById(targetId);
                const icon     = this.querySelector('i');
                const isHidden = input.type === 'password';
                input.type     = isHidden ? 'text' : 'password';
                icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        });
    </script>

</body>

</html>