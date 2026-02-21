{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name', 'Sistem Pembayaran Sekolah') }}</title>
    
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
        }

        * { font-family: 'Nunito', sans-serif; }

        body {
            min-height: 100vh;
            /* Gradient disamakan dengan sidebar aplikasi */
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, var(--primary-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px rgba(0,0,0,.3);
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 420px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .brand-icon {
            width: 70px;
            height: 70px;
            /* Menggunakan aksen Gold sesuai tema dashboard */
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            box-shadow: 0 8px 20px rgba(197, 160, 40, 0.3);
        }

        .login-title {
            text-align: center;
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: .25rem;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            text-align: center;
            color: #64748b;
            font-size: .875rem;
            margin-bottom: 2.5rem;
        }

        .form-label {
            font-weight: 600;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #475569;
        }

        .form-control {
            padding: .75rem 1rem;
            border-radius: .75rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            transition: all .2s;
        }

        .form-control:focus {
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 .25rem rgba(27, 75, 138, 0.15);
        }

        .btn-login {
            background: var(--primary);
            border: none;
            padding: .85rem;
            font-weight: 700;
            border-radius: .75rem;
            transition: all .3s;
            margin-top: 1rem;
            box-shadow: 0 4px 12px rgba(27, 75, 138, 0.2);
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(27, 75, 138, 0.3);
        }

        .alert {
            border: none;
            border-radius: .75rem;
            font-size: .85rem;
        }

        .login-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: .8rem;
            color: #94a3b8;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        
        <div class="login-title">Panel Administrasi</div>
        <div class="login-subtitle">Sistem Informasi Pembayaran Sekolah</div>

        {{-- Alerts --}}
        @if($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session('status'))
        <div class="alert alert-success shadow-sm">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Alamat Email</label>
                <div class="input-group">
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email') }}"
                           placeholder="nama@email.com" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Kata Sandi</label>
                <input type="password" name="password" class="form-control"
                       placeholder="••••••••" required>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input">
                    <label for="remember" class="form-check-label small text-muted">Ingat saya</label>
                </div>
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="small">Lupa Password?</a>
                @endif
            </div>

            <button type="submit" class="btn btn-primary btn-login w-100 text-white">
                Masuk ke Sistem <i class="bi bi-arrow-right-short ms-1"></i>
            </button>
        </form>

        <div class="login-footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}<br>
            Pusat Bantuan Layanan IT
        </div>
    </div>

</body>
</html>