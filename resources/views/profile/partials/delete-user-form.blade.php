{{-- resources/views/profile/partials/delete-user-form.blade.php --}}
<p class="text-muted small mb-3">
    Setelah akun dihapus, semua data terkait akan hilang secara permanen.
    Pastikan Anda sudah mengunduh data yang diperlukan sebelum melanjutkan.
</p>

<button type="button"
        class="btn btn-danger btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#modalHapusAkun">
    <i class="bi bi-trash3 me-1"></i>Hapus Akun
</button>

{{-- Modal Konfirmasi --}}
<div class="modal fade" id="modalHapusAkun" tabindex="-1" aria-labelledby="modalHapusAkunLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger fw-bold" id="modalHapusAkunLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Hapus Akun?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    Setelah akun dihapus, semua data dan informasi terkait akan dihapus secara permanen.
                    Masukkan password Anda untuk mengonfirmasi.
                </p>

                <form method="post" action="{{ route('profile.destroy') }}" id="formHapusAkun">
                    @csrf
                    @method('delete')

                    <div class="mb-3">
                        <label for="delete_password" class="form-label fw-semibold small">Password</label>
                        <input type="password"
                               id="delete_password"
                               name="password"
                               class="form-control @if($errors->userDeletion->get('password')) is-invalid @endif"
                               placeholder="Masukkan password Anda"
                               autofocus>
                        @if ($errors->userDeletion->get('password'))
                            <div class="invalid-feedback">
                                {{ $errors->userDeletion->first('password') }}
                            </div>
                        @endif
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="submit" form="formHapusAkun" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash3 me-1"></i>Ya, Hapus Akun
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Buka modal otomatis jika ada error validasi hapus akun --}}
@if ($errors->userDeletion->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('modalHapusAkun')).show();
    });
</script>
@endif