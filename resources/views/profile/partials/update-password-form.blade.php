{{-- resources/views/profile/partials/update-password-form.blade.php --}}
<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    {{-- Password Saat Ini --}}
    <div class="mb-3">
        <label for="current_password" class="form-label fw-semibold small">Password Saat Ini</label>
        <input type="password"
               id="current_password"
               name="current_password"
               class="form-control @if($errors->updatePassword->get('current_password')) is-invalid @endif"
               autocomplete="current-password">
        @if ($errors->updatePassword->get('current_password'))
            <div class="invalid-feedback">
                {{ $errors->updatePassword->first('current_password') }}
            </div>
        @endif
    </div>

    {{-- Password Baru --}}
    <div class="mb-3">
        <label for="password" class="form-label fw-semibold small">Password Baru</label>
        <input type="password"
               id="password"
               name="password"
               class="form-control @if($errors->updatePassword->get('password')) is-invalid @endif"
               autocomplete="new-password">
        @if ($errors->updatePassword->get('password'))
            <div class="invalid-feedback">
                {{ $errors->updatePassword->first('password') }}
            </div>
        @endif
    </div>

    {{-- Konfirmasi Password --}}
    <div class="mb-3">
        <label for="password_confirmation" class="form-label fw-semibold small">Konfirmasi Password Baru</label>
        <input type="password"
               id="password_confirmation"
               name="password_confirmation"
               class="form-control @if($errors->updatePassword->get('password_confirmation')) is-invalid @endif"
               autocomplete="new-password">
        @if ($errors->updatePassword->get('password_confirmation'))
            <div class="invalid-feedback">
                {{ $errors->updatePassword->first('password_confirmation') }}
            </div>
        @endif
    </div>

    <div class="d-flex align-items-center gap-3 mt-4">
        <button type="submit" class="btn btn-primary btn-sm px-4">
            <i class="bi bi-shield-check me-1"></i>Perbarui Password
        </button>
        @if (session('status') === 'password-updated')
            <span class="text-success small">
                <i class="bi bi-check-circle me-1"></i>Password berhasil diperbarui.
            </span>
        @endif
    </div>
</form>