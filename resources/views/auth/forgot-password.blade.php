{{-- resources/views/auth/forgot-password.blade.php --}}
<x-guest-layout>
    @section('title', 'Lupa Password')

    <div class="auth-title">Lupa Kata Sandi?</div>
    <div class="auth-subtitle">
        Masukkan email Anda dan kami akan mengirimkan tautan untuk membuat kata sandi baru.
    </div>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        {{-- Email --}}
        <div class="mb-4">
            <label for="email" class="form-label">Alamat Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="nama@sekolah.com"
                       autocomplete="email"
                       required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn-auth">
            <i class="bi bi-send me-2"></i> Kirim Tautan Reset
        </button>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="auth-link">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke halaman masuk
            </a>
        </div>
    </form>
</x-guest-layout>