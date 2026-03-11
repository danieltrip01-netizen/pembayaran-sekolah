{{-- resources/views/auth/verify-email.blade.php --}}
<x-guest-layout>
    @section('title', 'Verifikasi Email')

    {{-- Icon & heading --}}
    <div class="text-center mb-4">
        <div style="
            width:56px;height:56px;border-radius:14px;
            background:var(--blue-pale);border:1px solid var(--blue-light);
            display:inline-flex;align-items:center;justify-content:center;
            font-size:1.5rem;color:var(--blue);margin-bottom:.9rem;
        ">
            <i class="bi bi-envelope-check"></i>
        </div>
        <div class="auth-title">Verifikasi Email Anda</div>
        <p class="auth-subtitle mb-0">
            Sebelum melanjutkan, silakan cek kotak masuk email Anda dan klik tautan verifikasi yang kami kirimkan.
            Jika belum menerima email, kami akan mengirimkan ulang.
        </p>
    </div>

    {{-- Kirim ulang --}}
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn-auth mb-3">
            <i class="bi bi-arrow-clockwise me-2"></i> Kirim Ulang Email Verifikasi
        </button>
    </form>

    {{-- Divider --}}
    <div class="auth-divider"><span>atau</span></div>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-auth" style="
            background:transparent;
            border:1.5px solid var(--border);
            color:var(--ink-muted);
        "
        onmouseover="this.style.background='var(--bg)'"
        onmouseout="this.style.background='transparent'">
            <i class="bi bi-box-arrow-right me-2"></i> Keluar dari Akun
        </button>
    </form>
</x-guest-layout>