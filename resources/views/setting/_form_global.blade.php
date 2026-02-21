{{-- resources/views/setting/_form_global.blade.php --}}
{{-- Data bersama yayasan: nama, alamat, kota, telepon (tanpa logo — logo ada di tiap jenjang) --}}

<form method="POST" action="{{ route('setting.update') }}" enctype="multipart/form-data">
@csrf @method('PUT')
<input type="hidden" name="jenjang" value="global">

<div class="row g-4">

    {{-- ── Kiri: Info ─────────────────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3"
                     style="width:64px;height:64px;background:#eff6ff">
                    <i class="bi bi-building fs-3" style="color:#1d4ed8"></i>
                </div>
                <div class="fw-bold mb-1" style="color:var(--primary)">Data Yayasan</div>
                <p class="text-muted small mb-0">
                    Data ini bersifat global dan digunakan sebagai referensi pada semua dokumen cetak.
                    Logo masing-masing sekolah diatur di tab <strong>TK</strong>, <strong>SD</strong>, dan <strong>SMP</strong>.
                </p>
            </div>
        </div>
    </div>

    {{-- ── Kanan: Form ─────────────────────────────────────────────── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold" style="color:var(--primary)">
                    <i class="bi bi-building me-2"></i>Identitas Yayasan
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Nama Yayasan / Lembaga</label>
                        <input type="text" name="nama_yayasan" class="form-control"
                               value="{{ old('nama_yayasan', $setting->nama_yayasan) }}"
                               placeholder="Contoh: Yayasan Kristen Dorkas">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Alamat</label>
                        <input type="text" name="alamat" class="form-control"
                               value="{{ old('alamat', $setting->alamat) }}"
                               placeholder="Jl. Untung Suropati No. 23">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Kota</label>
                        <input type="text" name="kota" class="form-control"
                               value="{{ old('kota', $setting->kota) }}" placeholder="Lasem">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small">Telepon</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" name="telepon" class="form-control"
                                   value="{{ old('telepon', $setting->telepon) }}"
                                   placeholder="0291-123456">
                        </div>
                    </div>
                </div>

                {{-- Pratinjau teks header --}}
                <hr class="my-4">
                <div class="section-label">Pratinjau Teks Header Dokumen</div>
                <div class="doc-preview">
                    <div class="pb-2" style="border-bottom:2px solid #1B4B8A">
                        <div class="fw-bold" id="previewNamaYayasan" style="color:#1B4B8A">
                            {{ $setting->nama_yayasan ?: '[ Nama Yayasan ]' }}
                        </div>
                        <div class="text-muted" id="previewAlamat" style="font-size:.72rem">
                            {{ collect([$setting->alamat, $setting->kota])->filter()->join(', ') ?: '[ Alamat ]' }}
                        </div>
                        <div class="text-muted" id="previewTelepon" style="font-size:.72rem">
                            {{ $setting->telepon ? 'Telp. ' . $setting->telepon : '' }}
                        </div>
                    </div>
                    <div class="text-muted small mt-2 text-center" style="font-size:.7rem">
                        <i class="bi bi-image me-1"></i>Logo masing-masing sekolah diatur di tab TK / SD / SMP
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 px-4">
                <div class="text-muted small">
                    <i class="bi bi-clock me-1"></i>
                    Diperbarui: {{ $setting->updated_at?->isoFormat('D MMM Y, HH:mm') ?? '—' }}
                </div>
                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                    <i class="bi bi-floppy me-2"></i>Simpan Data Yayasan
                </button>
            </div>
        </div>
    </div>

</div>
</form>