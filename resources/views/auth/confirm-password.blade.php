{{-- resources/views/auth/confirm-password.blade.php --}}
<x-guest-layout>
    @section('title', 'Konfirmasi Kata Sandi')

    {{-- Icon & heading --}}
    <div class="text-center mb-4">
        <div style="
            width:56px;height:56px;border-radius:14px;
            background:#FEF9C3;border:1px solid #FDE68A;
            display:inline-flex;align-items:center;justify-content:center;
            font-size:1.4rem;color:#B45309;margin-bottom:.9rem;
        ">
            <i class="bi bi-shield-lock"></i>
        </div>
        <div class="auth-title">Area Terproteksi</div>
        <p class="auth-subtitle mb-0">
            Halaman ini memerlukan konfirmasi identitas. Masukkan kata sandi Anda untuk melanjutkan.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        {{-- Password --}}
        <div class="mb-4">
            <label for="password" class="form-label">Kata Sandi</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Masukkan kata sandi Anda"
                       autocomplete="current-password"
                       required autofocus>
                <button type="button" class="input-group-text bg-white border-start-0 pw-toggle"
                        data-target="password" aria-label="Tampilkan kata sandi">
                    <i class="bi bi-eye"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn-auth">
            <i class="bi bi-check-circle me-2"></i> Konfirmasi &amp; Lanjutkan
        </button>
    </form>
</x-guest-layout>