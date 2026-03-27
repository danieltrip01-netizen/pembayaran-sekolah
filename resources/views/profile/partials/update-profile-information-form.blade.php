{{-- resources/views/profile/partials/update-profile-information-form.blade.php --}}
<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    {{-- Nama --}}
    <div class="mb-3">
        <label for="name" class="form-label fw-semibold small">Nama</label>
        <input type="text"
               id="name"
               name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $user->name) }}"
               required
               autofocus
               autocomplete="name">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Email --}}
    <div class="mb-3">
        <label for="email" class="form-label fw-semibold small">Email</label>
        <input type="email"
               id="email"
               name="email"
               class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $user->email) }}"
               required
               autocomplete="username">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-2">
                <p class="text-warning small mb-1">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Email Anda belum diverifikasi.
                </p>
                <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">
                        Kirim ulang email verifikasi →
                    </button>
                </form>
                @if (session('status') === 'verification-link-sent')
                    <p class="text-success small mt-1">
                        <i class="bi bi-check-circle me-1"></i>Link verifikasi baru telah dikirim.
                    </p>
                @endif
            </div>
        @endif
    </div>

    <div class="d-flex align-items-center gap-3 mt-4">
        <button type="submit" class="btn btn-primary btn-sm px-4">
            <i class="bi bi-check2 me-1"></i>Simpan
        </button>
        @if (session('status') === 'profile-updated')
            <span class="text-success small">
                <i class="bi bi-check-circle me-1"></i>Tersimpan.
            </span>
        @endif
    </div>
</form>