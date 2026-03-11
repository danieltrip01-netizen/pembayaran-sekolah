{{-- resources/views/auth/register.blade.php --}}
<x-guest-layout>
    @section('title', 'Daftar Akun')

    <div class="auth-title">Buat Akun Baru</div>
    <div class="auth-subtitle">Isi data di bawah untuk mendaftarkan akun Anda</div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Nama Lengkap --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nama Lengkap</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text"
                       id="name"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}"
                       placeholder="Nama lengkap Anda"
                       autocomplete="name"
                       required autofocus>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">Alamat Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="nama@sekolah.com"
                       autocomplete="username"
                       required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Password --}}
        <div class="mb-3">
            <label for="password" class="form-label">Kata Sandi</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Minimal 8 karakter"
                       autocomplete="new-password"
                       required>
                <button type="button" class="input-group-text bg-white border-start-0 pw-toggle"
                        data-target="password" aria-label="Tampilkan kata sandi">
                    <i class="bi bi-eye"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Konfirmasi Password --}}
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       class="form-control @error('password_confirmation') is-invalid @enderror"
                       placeholder="Ulangi kata sandi"
                       autocomplete="new-password"
                       required>
                <button type="button" class="input-group-text bg-white border-start-0 pw-toggle"
                        data-target="password_confirmation" aria-label="Tampilkan konfirmasi">
                    <i class="bi bi-eye"></i>
                </button>
                @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn-auth">
            <i class="bi bi-person-plus me-2"></i> Buat Akun
        </button>

        <div class="text-center mt-3">
            <span style="font-size:.8rem;color:var(--ink-muted)">Sudah punya akun?</span>
            <a href="{{ route('login') }}" class="auth-link ms-1">Masuk di sini</a>
        </div>
    </form>
</x-guest-layout>