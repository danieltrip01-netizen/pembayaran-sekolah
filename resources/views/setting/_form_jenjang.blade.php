{{-- resources/views/setting/_form_jenjang.blade.php --}}
{{-- Variabel:
     $setting        : Setting model untuk jenjang ini
     $jenjang        : 'TK' | 'SD' | 'SMP'
     $isAdminYayasan : bool
--}}

@php
    $jColors = [
        'TK' => ['color' => '#db2777', 'bg' => '#fce7f3', 'border' => '#f9a8d4'],
        'SD' => ['color' => '#1d4ed8', 'bg' => '#dbeafe', 'border' => '#93c5fd'],
        'SMP' => ['color' => '#059669', 'bg' => '#d1fae5', 'border' => '#6ee7b7'],
    ];
    $jc = $jColors[$jenjang] ?? ['color' => '#64748b', 'bg' => '#f1f5f9', 'border' => '#e2e8f0'];

    // Semua data dibaca dari row jenjang — konsisten dengan CetakController.
    $namaYayasanVal = old('nama_yayasan', $setting->nama_yayasan ?? '');

    $logoIsJenjang = (bool) $setting->logo;
    $logoUrl       = $setting->logo
        ? \Illuminate\Support\Facades\Storage::url($setting->logo)
        : null;
@endphp

<form method="POST" action="{{ route('setting.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <input type="hidden" name="jenjang" value="{{ $jenjang }}">

    <div class="row g-4">

        {{-- ══ KIRI: Logo + Tanda Tangan ══════════════════════════════ --}}
        <div class="col-lg-4">

            {{-- ── Logo Sekolah ─────────────────────────────────────── --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold" style="color:var(--primary)">
                        <i class="bi bi-image me-2"></i>Logo Sekolah 
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Logo khusus . Gunakan PNG berlatar transparan untuk hasil terbaik.
                        Ukuran ideal: minimal 200×200 px.
                    </p>

                    <div class="text-center mb-3">
                        <div id="logoCurrWrap">
                            @if ($logoUrl)
                                <div class="preview-wrap">
                                    <img src="{{ $logoUrl }}" height="90" alt="Logo ">
                                    @if ($logoIsJenjang)
                                        <div class="btn-remove-img" id="btnHapus_logo" title="Hapus logo">
                                            <i class="bi bi-x"></i>
                                        </div>
                                    @endif
                                </div>
                                @if (!$logoIsJenjang)
                                    <div class="text-muted mt-1" style="font-size:.7rem">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Menampilkan logo global — upload logo khusus di bawah
                                    </div>
                                @endif
                            @else
                                <div class="text-muted small py-2">
                                    <i class="bi bi-image fs-1 d-block mb-1 opacity-20"></i>
                                    Belum ada logo
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="upload-zone text-center py-4 px-3 {{ $logoIsJenjang ? 'has-file' : '' }}"
                        id="logoDropZone" onclick="document.getElementById('logoInput').click()">
                        <i class="bi bi-cloud-arrow-up fs-3 mb-2 d-block" style="color:#1d4ed8;opacity:.7"></i>
                        <div class="small fw-semibold" style="color:#1d4ed8" id="logoLabel">
                            {{ $logoIsJenjang ? 'Ganti logo ' . $jenjang : 'Upload logo khusus ' . $jenjang }}
                        </div>
                        <div class="text-muted" style="font-size:.7rem">PNG, JPG · Maks 2 MB</div>
                    </div>
                    <input type="file" id="logoInput" name="logo" accept="image/png,image/jpeg" class="d-none">
                    <input type="hidden" name="hapus_logo" id="hapus_logo" value="0">

                    <div class="text-center mt-3 d-none" id="logoNewPreview">
                        <div class="preview-wrap">
                            <img src="" id="logoNewImg" height="80" alt="Preview">
                            <div class="btn-remove-img" id="btnBatal_logo" title="Batalkan">
                                <i class="bi bi-x"></i>
                            </div>
                        </div>
                        <div class="text-success small mt-1" id="logoNewName"></div>
                    </div>
                </div>
            </div>

            {{-- ── Tanda Tangan ──────────────────────────────────────── --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold" style="color:var(--primary)">
                        <i class="bi bi-pen me-2"></i>Tanda Tangan
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Tanda tangan kepala sekolah untuk dokumen cetak.
                        Gunakan PNG berlatar putih atau transparan.
                    </p>

                    <div class="text-center mb-3">
                        <div id="ttdCurrWrap">
                            @if ($setting->tanda_tangan_url)
                                <div class="preview-wrap">
                                    <img src="{{ $setting->tanda_tangan_url }}" height="70" alt="Tanda Tangan">
                                    <div class="btn-remove-img" id="btnHapus_ttd" title="Hapus">
                                        <i class="bi bi-x"></i>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted small py-2">
                                    <i class="bi bi-pen fs-1 d-block mb-1 opacity-20"></i>
                                    Belum ada tanda tangan
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="upload-zone text-center py-4 px-3 {{ $setting->tanda_tangan ? 'has-file' : '' }}"
                        id="ttdDropZone" onclick="document.getElementById('ttdInput').click()">
                        <i class="bi bi-cloud-arrow-up fs-3 mb-2 d-block" style="color:#1d4ed8;opacity:.7"></i>
                        <div class="small fw-semibold" style="color:#1d4ed8" id="ttdLabel">
                            {{ $setting->tanda_tangan ? 'Ganti tanda tangan' : 'Pilih atau seret file' }}
                        </div>
                        <div class="text-muted" style="font-size:.7rem">PNG, JPG · Maks 2 MB</div>
                    </div>
                    <input type="file" id="ttdInput" name="tanda_tangan" accept="image/png,image/jpeg"
                        class="d-none">
                    <input type="hidden" name="hapus_tanda_tangan" id="hapus_ttd" value="0">

                    <div class="text-center mt-3 d-none" id="ttdNewPreview">
                        <div class="preview-wrap">
                            <img src="" id="ttdNewImg" height="60" alt="Preview">
                            <div class="btn-remove-img" id="btnBatal_ttd" title="Batalkan">
                                <i class="bi bi-x"></i>
                            </div>
                        </div>
                        <div class="text-success small mt-1" id="ttdNewName"></div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ══ KANAN: Identitas + Preview ══════════════════════════════ --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold" style="color:var(--primary)">
                        <i class="bi bi-people me-2"></i>Data Sekolah
                    </h6>
                </div>
                <div class="card-body p-4">

                    {{-- ── Identitas Sekolah ──────────────────────────── --}}
                    <div class="section-label">Identitas Sekolah</div>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                Nama Sekolah
                            </label>
                            <input type="text" name="nama_sekolah"
                                class="form-control @error('nama_sekolah') is-invalid @enderror"
                                value="{{ old('', $setting->nama_sekolah) }}" placeholder="">
                            @error('nama_sekolah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Nama Yayasan / Lembaga</label>
                            <input type="text" name="nama_yayasan"
                                class="form-control @error('nama_yayasan') is-invalid @enderror"
                                value="{{ $namaYayasanVal }}" placeholder="">
                            @error('nama_yayasan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- ── Alamat & Kontak ────────────────────────────── --}}
                    <div class="section-label">Alamat &amp; Kontak</div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Alamat</label>
                            <input type="text" name="alamat" class="form-control"
                                value="{{ old('', $setting->alamat) }}"
                                placeholder="">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Kota</label>
                            <input type="text" name="kota" class="form-control"
                                value="{{ old('', $setting->kota) }}" placeholder="">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="telepon" class="form-control"
                                    value="{{ old('', $setting->telepon) }}" placeholder="">
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- ── Kepala Sekolah ─────────────────────────────── --}}
                    <div class="section-label">Kepala Sekolah</div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small">Nama Kepala Sekolah</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                <input type="text" name="nama_kepala_sekolah" class="form-control"
                                    value="{{ old('', $setting->nama_kepala_sekolah) }}"
                                    placeholder="">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">NIP</label>
                            <input type="text" name="nip_kepala_sekolah" class="form-control"
                                value="{{ old('', $setting->nip_kepala_sekolah) }}"
                                placeholder="">
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- ── Admin / Bendahara ──────────────────────────── --}}
                    <div class="section-label">Admin / Bendahara</div>
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small">Nama Admin / Bendahara</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-gear"></i></span>
                                <input type="text" name="nama_admin" class="form-control"
                                    value="{{ old('', $setting->nama_admin) }}"
                                    placeholder="">
                            </div>
                            <div class="form-text text-muted small">
                                Muncul di bagian tanda tangan pada dokumen cetak.
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- ── Pratinjau dokumen ──────────────────────────── --}}
                    <div class="section-label">Pratinjau Dokumen Cetak</div>
                    <div class="doc-preview">

                        {{-- Header --}}
                        <div class="d-flex align-items-center gap-3 pb-2" style="border-bottom:2px solid #1B4B8A">
                            {{-- Selalu <img> agar JS live-preview cukup update .src --}}
                            <img src="{{ $logoUrl ?? '' }}"
                                 height="52" id="previewLogoImg" alt="Logo"
                                 class="flex-shrink-0 rounded"
                                 style="object-fit:contain;background:#f8fafc;border:1px solid #e2e8f0;{{ $logoUrl ? '' : 'display:none' }}">
                            <div>
                                <div class="text-muted" style="font-size:.72rem" id="previewNamaYayasan">
                                    {{ $namaYayasanVal ?: '[ Nama Yayasan ]' }}
                                </div>
                                <div class="fw-bold" id="previewNamaSekolah" style="color:#1B4B8A">
                                    {{ $setting->nama_sekolah ?: '[ Nama Sekolah ]' }}
                                </div>
                                <div class="text-muted" id="previewAlamat" style="font-size:.72rem">
                                    {{ collect([$setting->alamat, $setting->kota])->filter()->join(', ') ?:'[ Alamat Sekolah ]' }}
                                </div>
                                <div class="text-muted" id="previewTelepon" style="font-size:.72rem">
                                    {{ $setting->telepon ? 'Telp. ' . $setting->telepon : '' }}
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="d-flex justify-content-between mt-3" style="font-size:.75rem">
                            <div class="text-center" style="width:45%">
                                <p class="mb-0">Mengetahui,</p>
                                <p class="mb-0">Kepala Sekolah</p>
                                @if ($setting->tanda_tangan_url)
                                    <img src="{{ $setting->tanda_tangan_url }}" height="40" id="previewTtdImg"
                                        alt="TTD" style="margin:2px 0">
                                @else
                                    <div id="previewTtdImg"
                                        class="d-flex align-items-center justify-content-center mx-auto"
                                        style="width:80px;height:40px;border:1px dashed #cbd5e1;border-radius:4px">
                                        <i class="bi bi-pen text-muted" style="font-size:.7rem"></i>
                                    </div>
                                @endif
                                <div style="border-top:1px solid #ccc;padding-top:2px">
                                    <strong id="previewKepala">
                                        {{ $setting->nama_kepala_sekolah ?: '( Nama Kepala Sekolah )' }}
                                    </strong>
                                </div>
                            </div>
                            <div class="text-center" style="width:45%">
                                <p class="mb-0">Bendahara,</p>
                                <div style="height:50px"></div>
                                <div style="border-top:1px solid #ccc;padding-top:2px">
                                    <strong id="previewAdmin">
                                        {{ $setting->nama_admin ?: '( Nama Admin )' }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 px-4">
                    <div class="text-muted small">
                        <i class="bi bi-clock me-1"></i>
                        Diperbarui: {{ $setting->updated_at?->isoFormat('D MMM Y, HH:mm') ?? '—' }}
                    </div>
                    <button type="submit" class="btn px-4 shadow-sm text-white"
                        style="background:{{ $jc['color'] }};border-color:{{ $jc['color'] }}">
                        <i class="bi bi-floppy me-2"></i>Simpan Data
                    </button>
                </div>
            </div>
        </div>

    </div>
</form>