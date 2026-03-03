{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — {{ config('app.name', 'EduPay') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy:        #0C1E3E;
            --navy-mid:    #142952;
            --navy-soft:   #1C3566;
            --blue:        #2563EB;
            --blue-mid:    #3B82F6;
            --blue-light:  #BFDBFE;
            --blue-pale:   #EFF6FF;
            --ink:         #0F172A;
            --ink-soft:    #334155;
            --ink-muted:   #64748B;
            --border:      #E2E8F0;
            --bg:          #F8FAFC;
            --surface:     #FFFFFF;
            --green:       #10B981;
            --green-pale:  #D1FAE5;
            --red:         #EF4444;
            --red-pale:    #FEF2F2;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            background: var(--bg);
            display: flex;
            align-items: stretch;
        }

        /* ============================================================
           LEFT PANEL
        ============================================================ */
        .panel-left {
            width: 42%;
            background: var(--navy);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2.75rem 3rem;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        /* Subtle geometric line grid */
        .panel-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        /* Top-right accent corner */
        .panel-left::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 220px; height: 220px;
            background: linear-gradient(225deg, rgba(37,99,235,.18) 0%, transparent 60%);
            pointer-events: none;
        }

        /* Bottom left accent */
        .panel-accent-bottom {
            position: absolute;
            bottom: 0; left: 0;
            width: 280px; height: 160px;
            background: linear-gradient(45deg, rgba(37,99,235,.12) 0%, transparent 60%);
            pointer-events: none;
        }

        /* Left content */
        .left-top  { position: relative; z-index: 1; }
        .left-bottom { position: relative; z-index: 1; }

        .left-logo {
            display: flex;
            align-items: center;
            gap: .85rem;
            margin-bottom: 3.5rem;
        }

        .left-logo-icon {
            width: 40px; height: 40px;
            background: var(--blue);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            color: #fff;
            flex-shrink: 0;
        }

        .left-logo-name {
            font-family: 'Sora', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            letter-spacing: .3px;
        }

        .left-heading {
            font-family: 'Sora', sans-serif;
            font-size: 2.4rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 1.1rem;
            letter-spacing: -.5px;
        }

        .left-heading .highlight {
            color: var(--blue-mid);
        }

        .left-desc {
            font-size: .88rem;
            color: rgba(255,255,255,.72);
            line-height: 1.8;
            max-width: 300px;
            font-weight: 300;
        }

        /* Stats strip */
        .stats-strip {
            display: flex;
            gap: 0;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 10px;
            overflow: hidden;
            background: rgba(255,255,255,.03);
        }

        .strip-item {
            flex: 1;
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: .2rem;
        }

        .strip-item + .strip-item {
            border-left: 1px solid rgba(255,255,255,.08);
        }

        .strip-num {
            font-family: 'Sora', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            line-height: 1;
        }

        .strip-lbl {
            font-size: .65rem;
            color: rgba(255,255,255,.55);
            font-weight: 500;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .left-footer {
            font-size: .67rem;
            color: rgba(255,255,255,.35);
            margin-top: 1rem;
        }

        /* ============================================================
           RIGHT PANEL
        ============================================================ */
        .panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
            position: relative;
            background: var(--bg);
        }

        .login-box {
            width: 100%;
            max-width: 380px;
            position: relative;
            z-index: 1;
        }

        /* System tag */
        .system-tag {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: var(--blue-pale);
            border: 1px solid var(--blue-light);
            color: var(--blue);
            font-size: .7rem;
            font-weight: 600;
            padding: .3rem .85rem;
            border-radius: 999px;
            margin-bottom: 1.4rem;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .system-tag .dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--blue);
        }

        .login-title {
            font-family: 'Sora', sans-serif;
            font-size: 1.85rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: .4rem;
            line-height: 1.15;
            letter-spacing: -.4px;
        }

        .login-subtitle {
            font-size: .87rem;
            color: var(--ink-muted);
            margin-bottom: 2.25rem;
            font-weight: 400;
        }

        /* Divider */
        .form-divider {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 1.75rem;
        }

        .form-divider::before, .form-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .form-divider span {
            font-size: .67rem;
            font-weight: 600;
            color: var(--ink-muted);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Input group */
        .field-block { margin-bottom: 1.15rem; }

        .field-label {
            display: block;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--ink-soft);
            margin-bottom: .45rem;
        }

        .field-input-wrap {
            position: relative;
        }

        .field-prefix {
            position: absolute;
            left: .9rem; top: 50%;
            transform: translateY(-50%);
            color: var(--ink-muted);
            font-size: .85rem;
            pointer-events: none;
            transition: color .18s;
        }

        .field-suffix {
            position: absolute;
            right: .85rem; top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--ink-muted);
            cursor: pointer;
            font-size: .85rem;
            padding: 0;
            line-height: 1;
            transition: color .18s;
        }

        .field-suffix:hover { color: var(--blue); }

        .field-input {
            width: 100%;
            padding: .72rem 2.5rem .72rem 2.5rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            color: var(--ink-soft);
            outline: none;
            transition: border-color .18s, box-shadow .18s;
        }

        .field-input::placeholder { color: #CBD5E1; font-weight: 300; }

        .field-input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }

        .field-input-wrap:focus-within .field-prefix {
            color: var(--blue);
        }

        /* Extras row */
        .form-extras {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.75rem;
        }

        .check-label {
            display: flex;
            align-items: center;
            gap: .45rem;
            cursor: pointer;
        }

        .check-label input[type="checkbox"] {
            width: 15px; height: 15px;
            border-radius: 4px;
            accent-color: var(--blue);
            cursor: pointer;
        }

        .check-label span {
            font-size: .82rem;
            color: var(--ink-muted);
        }

        .link-forgot {
            font-size: .82rem;
            font-weight: 600;
            color: var(--blue);
            text-decoration: none;
            transition: color .15s;
        }

        .link-forgot:hover { color: #1D4ED8; }

        /* Submit */
        .btn-masuk {
            width: 100%;
            padding: .88rem;
            background: var(--navy);
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            transition: all .2s ease;
            letter-spacing: .3px;
        }

        .btn-masuk:hover {
            background: var(--navy-soft);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(12,30,62,.25);
        }

        .btn-masuk:active { transform: translateY(0); }

        .btn-masuk .arrow-icon {
            width: 22px; height: 22px;
            border-radius: 50%;
            border: 1.5px solid rgba(255,255,255,.25);
            display: flex; align-items: center; justify-content: center;
            font-size: .72rem;
        }

        /* Alerts */
        .form-msg {
            border: none;
            border-radius: 8px;
            font-size: .84rem;
            font-weight: 500;
            padding: .85rem 1rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: .5rem;
        }

        .form-msg-danger  { background: var(--red-pale);  color: #991B1B; border-left: 3px solid var(--red); }
        .form-msg-success { background: var(--green-pale); color: #065F46; border-left: 3px solid var(--green); }

        /* Footer */
        .right-foot {
            margin-top: 1.75rem;
            text-align: center;
            font-size: .73rem;
            color: var(--ink-muted);
        }

        .right-foot a {
            color: var(--blue);
            font-weight: 600;
            text-decoration: none;
        }

        .right-foot a:hover { text-decoration: underline; }

        /* ============================================================
           RESPONSIVE
        ============================================================ */
        @media (max-width: 860px) {
            .panel-left { display: none; }
        }

        @media (max-width: 480px) {
            .panel-right { padding: 2rem 1.25rem; }
            .login-title  { font-size: 1.65rem; }
        }
    </style>
</head>
<body>

    <!-- LEFT PANEL -->
    <div class="panel-left">
        <div class="panel-accent-bottom"></div>

        <div class="left-top">
            <div class="left-logo">
                <div class="left-logo-icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <span class="left-logo-name">{{ config('app.name', 'EduPay') }}</span>
            </div>

            <div class="left-heading">
                Kelola <span class="highlight">Pembayaran</span><br>
                Sekolah Lebih<br>
                Efisien
            </div>

            <p class="left-desc">
                Platform administrasi keuangan sekolah yang modern, transparan, dan terpercaya untuk seluruh civitas akademika.
            </p>
        </div>

        <div class="left-bottom">
            <div class="stats-strip">
                <div class="strip-item">
                    <div class="strip-num">3</div>
                    <div class="strip-lbl">Jenjang</div>
                </div>
                <div class="strip-item">
                    <div class="strip-num">100%</div>
                    <div class="strip-lbl">Aman</div>
                </div>
                <div class="strip-item">
                    <div class="strip-num">24/7</div>
                    <div class="strip-lbl">Akses</div>
                </div>
            </div>
            <div class="left-footer" style="margin-top:1.25rem">
                &copy; {{ date('Y') }} {{ config('app.name') }} &nbsp;·&nbsp; Pusat Layanan IT
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="panel-right">
        <div class="login-box">

            <div class="system-tag">
                <span class="dot"></span>
                Portal Admin
            </div>

            <div class="login-title">Masuk ke Sistem</div>
            <div class="login-subtitle">Masukkan kredensial Anda untuk melanjutkan</div>

            <div class="form-divider"><span>Login Admin</span></div>

            {{-- Error messages --}}
            @if($errors->any())
            <div class="form-msg form-msg-danger">
                <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;margin-top:.1rem"></i>
                <div>
                    @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(session('status'))
            <div class="form-msg form-msg-success">
                <i class="bi bi-check-circle-fill" style="flex-shrink:0;margin-top:.1rem"></i>
                <span>{{ session('status') }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field-block">
                    <label class="field-label" for="email">Email</label>
                    <div class="field-input-wrap">
                        <i class="bi bi-envelope field-prefix"></i>
                        <input type="email"
                               id="email"
                               name="email"
                               class="field-input"
                               value="{{ old('email') }}"
                               placeholder="nama@sekolah.com"
                               autocomplete="email"
                               required
                               autofocus>
                    </div>
                </div>

                <div class="field-block">
                    <label class="field-label" for="password">Kata Sandi</label>
                    <div class="field-input-wrap">
                        <i class="bi bi-lock field-prefix"></i>
                        <input type="password"
                               id="password"
                               name="password"
                               class="field-input"
                               placeholder="Masukkan kata sandi"
                               autocomplete="current-password"
                               required>
                        <button type="button" class="field-suffix" id="pwToggle" aria-label="Lihat kata sandi">
                            <i class="bi bi-eye" id="pwIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-extras">
                    <label class="check-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Ingat saya</span>
                    </label>
                    @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="link-forgot">Lupa password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-masuk">
                    Masuk ke Sistem
                    <span class="arrow-icon"><i class="bi bi-arrow-right"></i></span>
                </button>
            </form>

            <div class="right-foot">
                Butuh bantuan? <a href="#">Hubungi IT Support</a>
            </div>

        </div>
    </div>

    <script>
        const pwToggle = document.getElementById('pwToggle');
        const pwInput  = document.getElementById('password');
        const pwIcon   = document.getElementById('pwIcon');

        pwToggle?.addEventListener('click', function () {
            const show = pwInput.type === 'password';
            pwInput.type = show ? 'text' : 'password';
            pwIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>

</body>
</html>