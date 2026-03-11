{{-- resources/views/auth/reset-password.blade.php --}}
<x-guest-layout>
    @section('title', 'Reset Password')

    <div class="auth-title">Buat Kata Sandi Baru</div>
    <div class="auth-subtitle">Masukkan kata sandi baru Anda di bawah ini</div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        {{-- Token tersembunyi --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">Alamat Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $request->email) }}"
                       placeholder="nama@sekolah.com"
                       autocomplete="username"
                       required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Password Baru --}}
        <div class="mb-3">
            <label for="password" class="form-label">Kata Sandi Baru</label>
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
            <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       class="form-control @error('password_confirmation') is-invalid @enderror"
                       placeholder="Ulangi kata sandi baru"
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
            <i class="bi bi-shield-check me-2"></i> Simpan Kata Sandi Baru
        </button>
    </form>
</x-guest-layout>