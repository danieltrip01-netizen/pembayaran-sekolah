{{-- resources/views/siswa/import.blade.php --}}
@extends('layouts.app')
@section('title', 'Import / Export Data Siswa')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.index') }}">Data Siswa</a></li>
    <li class="breadcrumb-item active">Import / Export Excel</li>
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--navy); font-family:'Sora',sans-serif;">Import / Export Data Siswa</h4>
            <p class="mb-0" style="color:var(--ink-muted);font-size:.85rem;">
                Kelola data siswa via Excel
                @if($tahunPelajaran)
                    &nbsp;·&nbsp;
                    <span class="badge" style="background:var(--blue-pale);color:var(--blue-dark);
                          border:1px solid var(--blue-light);font-size:.72rem;font-weight:600;">
                        <i class="bi bi-calendar-check me-1"></i>T.A. {{ $tahunPelajaran->nama }}
                    </span>
                @endif
            </p>
        </div>
        <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    {{-- ═══ HASIL IMPORT ═══ --}}
    @if (session('import_summary'))
        @php
            $ok             = session('import_ok', 0);
            $importFailures = session('import_failures', []);
        @endphp
        <div class="card mb-4"
             style="border-left: 4px solid {{ $ok > 0 ? '#059669' : '#dc2626' }} !important;">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:42px;height:42px;background:{{ $ok > 0 ? '#d1fae5' : '#fee2e2' }};">
                        <i class="bi {{ $ok > 0 ? 'bi-check-lg text-success' : 'bi-exclamation-triangle text-danger' }} fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-600 mb-1" style="color:var(--ink);">Hasil Import</div>
                        <p class="mb-0" style="font-size:.85rem;color:var(--ink-soft);">{!! session('import_summary') !!}</p>
                    </div>
                    <a href="{{ route('siswa.index') }}" class="btn btn-sm btn-outline-primary flex-shrink-0">
                        <i class="bi bi-list-ul me-1"></i>Lihat Data Siswa
                    </a>
                </div>

                @if (count($importFailures) > 0)
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-danger" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseErrors">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            {{ count($importFailures) }} baris gagal — klik untuk detail
                        </button>
                        <div class="collapse mt-2" id="collapseErrors">
                            <div class="table-responsive rounded-3 border">
                                <table class="table table-sm mb-0" style="font-size:.82rem;">
                                    <thead style="background:#fef2f2;">
                                        <tr>
                                            <th style="width:80px;color:var(--ink-soft);">Baris</th>
                                            <th style="width:200px;color:var(--ink-soft);">Nama</th>
                                            <th style="color:var(--ink-soft);">Pesan Error</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($importFailures as $err)
                                        <tr>
                                            <td class="fw-600" style="color:var(--red);">Baris {{ $err['row'] }}</td>
                                            <td style="color:var(--ink-soft);">{{ $err['nama'] }}</td>
                                            <td style="color:var(--red);font-size:.8rem;">{{ $err['pesan'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="row g-4">

        {{-- ═══ Form Upload ═══ --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"
                     style="background: var(--navy); color: #fff; border-radius: var(--r-xl) var(--r-xl) 0 0 !important;">
                    <h6 class="mb-0 fw-bold" style="color:#fff;">
                        <i class="bi bi-upload me-2"></i>Upload File Excel
                    </h6>
                </div>
                <div class="card-body">


                    {{-- ── Mode Import ── --}}
                    <div class="mb-4">
                        <label class="form-label fw-600" style="font-size:.85rem;">Mode Import</label>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="import_mode_ui"
                                   id="modeNew" value="new" checked autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm flex-grow-1" for="modeNew">
                                <i class="bi bi-person-plus me-1"></i>
                                <span class="fw-600">Tambah Baru</span><br>
                                <small class="text-muted fw-400">Daftarkan siswa baru ke sistem</small>
                            </label>

                            <input type="radio" class="btn-check" name="import_mode_ui"
                                   id="modeUpdate" value="update" autocomplete="off">
                            <label class="btn btn-outline-warning btn-sm flex-grow-1" for="modeUpdate">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                <span class="fw-600">Update</span><br>
                                <small class="text-muted fw-400">Perbarui kelas & nominal dari file export</small>
                            </label>
                        </div>
                        <div id="infoModeNew" class="rounded-3 mt-2 py-2 px-3"
                             style="background:#f0fdf4;border:1px solid #bbf7d0;font-size:.8rem;color:#166534;">
                            <i class="bi bi-info-circle me-1"></i>
                            Gunakan <strong>template kosong</strong>. Siswa baru akan didaftarkan ke
                            tahun pelajaran aktif.
                        </div>
                        <div id="infoModeUpdate" class="rounded-3 mt-2 py-2 px-3 d-none"
                             style="background:#fffbeb;border:1px solid #fde68a;font-size:.8rem;color:#92400e;">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            Gunakan file <strong>hasil export</strong>. Kolom <code>id_siswa</code> wajib ada
                            dan tidak boleh diubah. Siswa yang tidak ada di DB akan dilewati.
                        </div>
                    </div>

                    <form method="POST" action="{{ route('siswa.import.store') }}"
                          enctype="multipart/form-data" id="formImport">
                        @csrf
                        {{-- Field hidden yang akan diisi JS sesuai pilihan mode --}}
                        <input type="hidden" name="import_mode" id="importModeHidden" value="new">

                        <div id="dropZone" class="rounded-3 text-center py-5 px-4"
                             style="border:2px dashed #93c5fd;background:#eff6ff;
                                    transition:background .15s,border-color .15s;cursor:pointer;">
                            <i class="bi bi-file-earmark-excel d-block mb-2"
                               style="font-size:2rem;color:var(--blue-dark);"></i>
                            <div class="fw-600 mb-1" style="color:var(--blue-dark);">
                                Seret &amp; lepas file di sini
                            </div>
                            <div class="mb-3" style="color:var(--ink-muted);font-size:.82rem;">
                                Format: .xlsx, .xls, .csv — maks. 5 MB
                            </div>
                            <label for="fileInput" class="btn btn-primary btn-sm mb-0" style="cursor:pointer;">
                                <i class="bi bi-folder2-open me-1"></i>Pilih File
                            </label>
                            <input type="file" id="fileInput" name="file"
                                   accept=".xlsx,.xls,.csv" class="d-none">
                        </div>

                        <div id="filePreview" class="d-none mt-3">
                            <div class="rounded-3 p-3 d-flex align-items-center gap-3"
                                 style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                <i class="bi bi-file-earmark-check-fill text-success fs-4 flex-shrink-0"></i>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-600 text-truncate" id="previewName" style="font-size:.85rem;">—</div>
                                    <div id="previewSize" style="font-size:.75rem;color:var(--ink-muted);">—</div>
                                </div>
                                <button type="button" id="btnClear" class="btn btn-sm btn-outline-danger flex-shrink-0">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-success px-4" id="btnImport" disabled>
                                <i class="bi bi-upload me-2"></i>Mulai Import
                            </button>

                            {{-- Tombol Template (mode = new) --}}
                            <a href="{{ route('siswa.import.template') }}"
                               class="btn btn-outline-secondary" id="btnTemplate">
                                <i class="bi bi-file-earmark-excel me-1"></i>
                                <span id="btnTemplateLabel">Template Baru</span>
                            </a>

                            {{-- Dropdown Export (mode = update) — hidden saat mode new --}}
                            <div class="btn-group d-none" id="btnGroupExport">
                                <a href="{{ route('siswa.import.export', ['sumber' => 'sebelumnya']) }}"
                                   class="btn btn-outline-warning">
                                    <i class="bi bi-file-earmark-arrow-down me-1"></i>
                                    Export Tahun Sebelumnya
                                    @if($tahunSebelumnya)
                                        <span class="badge ms-1"
                                              style="background:var(--blue-pale);color:var(--blue-dark);
                                                     border:1px solid var(--blue-light);font-size:.65rem;">
                                            {{ $tahunSebelumnya->nama }}
                                        </span>
                                    @endif
                                </a>
                                <button type="button"
                                        class="btn btn-outline-warning dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item"
                                           href="{{ route('siswa.import.export', ['sumber' => 'sebelumnya']) }}">
                                            <i class="bi bi-clock-history me-2 text-warning"></i>
                                            Dari T.A. <strong>{{ $tahunSebelumnya?->nama ?? '—' }}</strong>
                                            <span class="d-block text-muted" style="font-size:.75rem;">
                                                Data siswa tahun sebelumnya
                                            </span>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item"
                                           href="{{ route('siswa.import.export', ['sumber' => 'aktif']) }}">
                                            <i class="bi bi-calendar-check me-2 text-success"></i>
                                            Dari T.A. <strong>{{ $tahunPelajaran?->nama ?? '—' }}</strong>
                                            <span class="d-block text-muted" style="font-size:.75rem;">
                                                Data siswa tahun aktif saat ini
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div id="importProgress" class="mt-3 d-none">
                            <div class="progress" style="height:6px;border-radius:99px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success w-100"></div>
                            </div>
                            <div style="font-size:.78rem;color:var(--ink-muted);" class="mt-1">
                                <i class="bi bi-hourglass-split me-1"></i>Sedang memproses...
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ═══ Panduan ═══ --}}
        <div class="col-lg-5 d-flex flex-column gap-4">

            {{-- Kolom file baru --}}
            <div class="card" id="panduanNew">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                        <i class="bi bi-table me-2"></i>Kolom (Tambah Baru)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size:.8rem;">
                            <thead style="background:var(--bg);">
                                <tr>
                                    <th class="ps-4">Kolom</th>
                                    <th>Wajib</th>
                                    <th class="pe-4">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $w   = '<span class="badge" style="background:#fee2e2;color:#dc2626;border:1px solid #fecaca;font-size:.62rem;">Wajib</span>';
                                    $opt = '<span style="color:var(--ink-faint);font-size:.75rem;">Opsional</span>';
                                    $colsNew = [
                                        ['nama',            $w,   'Nama lengkap siswa'],
                                        ['jenjang',         $w,   '<code>TK</code> / <code>SD</code> / <code>SMP</code>'],
                                        ['kelas',           $w,   'KB/OA/OB (TK) · I–VI (SD) · VII–IX (SMP)'],
                                        ['no_hp_wali',      $opt, 'No. HP wali (contoh: 08123456789)'],
                                        ['nominal_spp',     $opt, 'SPP per bulan (angka, mis: 175000)'],
                                        ['nominal_donator', $opt, 'Keringanan SPP (0 jika tidak ada)'],
                                        ['nominal_mamin',   $opt, 'Makan &amp; minum — hanya untuk TK'],
                                    ];
                                @endphp
                                @foreach ($colsNew as [$col, $badge, $ket])
                                <tr style="{{ $loop->even ? 'background:var(--bg);' : '' }}">
                                    <td class="ps-4 py-2"><code style="color:var(--navy);">{{ $col }}</code></td>
                                    <td class="py-2">{!! $badge !!}</td>
                                    <td class="pe-4 py-2" style="color:var(--ink-muted);">{!! $ket !!}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3 border-top" style="background:#f0fdf4;">
                        <div class="fw-600 mb-1" style="font-size:.78rem;color:#059669;">
                            <i class="bi bi-magic me-1"></i>Digenerate otomatis:
                        </div>
                        <ul class="mb-0 ps-3" style="color:var(--ink-soft);font-size:.78rem;line-height:2;">
                            <li><strong>ID Siswa</strong> — berdasarkan jenjang</li>
                            <li>
                                <strong>Tanggal Masuk</strong> —
                                @if($tahunPelajaran)
                                    {{ $tahunPelajaran->tanggal_mulai->translatedFormat('d F Y') }}
                                    <span style="color:var(--ink-faint);">(tanggal mulai T.A. aktif)</span>
                                @else
                                    <span class="text-danger">Tidak ada T.A. aktif</span>
                                @endif
                            </li>
                            <li><strong>Status</strong> — aktif</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Kolom file update --}}
            <div class="card d-none" id="panduanUpdate">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                        <i class="bi bi-table me-2"></i>Kolom (Update)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size:.8rem;">
                            <thead style="background:var(--bg);">
                                <tr>
                                    <th class="ps-4">Kolom</th>
                                    <th>Wajib</th>
                                    <th class="pe-4">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $colsUpdate = [
                                        ['id_siswa',        $w,   'ID dari hasil export — JANGAN DIUBAH'],
                                        ['nama',            $w,   'Nama siswa (boleh diperbarui)'],
                                        ['jenjang',         $w,   '<code>TK</code> / <code>SD</code> / <code>SMP</code>'],
                                        ['kelas',           $w,   'Kelas di tahun baru (boleh diubah)'],
                                        ['no_hp_wali',      $opt, '✏️ No. HP wali (boleh diperbarui)'],
                                        ['nominal_spp',     $opt, '✏️ Edit sesuai tarif tahun baru'],
                                        ['nominal_donator', $opt, '✏️ Edit sesuai tarif tahun baru'],
                                        ['nominal_mamin',   $opt, '✏️ Hanya untuk TK'],
                                        ['status',          $opt, 'aktif / tidak_aktif'],
                                    ];
                                @endphp
                                @foreach ($colsUpdate as [$col, $badge, $ket])
                                <tr style="{{ $loop->even ? 'background:var(--bg);' : '' }}">
                                    <td class="ps-4 py-2">
                                        <code style="color:{{ $col === 'id_siswa' ? '#dc2626' : 'var(--navy)' }};">{{ $col }}</code>
                                    </td>
                                    <td class="py-2">{!! $badge !!}</td>
                                    <td class="pe-4 py-2" style="color:var(--ink-muted);">{!! $ket !!}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3 border-top" style="background:#fffbeb;">
                        <div class="fw-600 mb-1" style="font-size:.78rem;color:#92400e;">
                            <i class="bi bi-exclamation-circle me-1"></i>Alur kerja tahun pelajaran baru:
                        </div>
                        <ol class="mb-0 ps-3" style="color:var(--ink-soft);font-size:.78rem;line-height:2.2;">
                            <li>Aktifkan tahun pelajaran baru di menu <strong>Tahun Pelajaran</strong></li>
                            <li>Klik <strong>Export Excel</strong> di halaman ini</li>
                            <li>Edit kolom kelas dan nominal di Excel</li>
                            <li>Upload kembali dengan mode <strong>Update</strong></li>
                        </ol>
                    </div>
                </div>
            </div>

            {{-- Catatan umum --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                        <i class="bi bi-lightbulb me-2"></i>Catatan
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0" style="font-size:.85rem;line-height:2.2;color:var(--ink-soft);">
                        <li><i class="bi bi-x-circle me-2" style="color:var(--red);"></i>
                            Baris gagal validasi dilewati, proses tetap berjalan
                        </li>
                        <li><i class="bi bi-file-earmark-excel me-2" style="color:var(--green);"></i>
                            Format: <code>.xlsx</code>, <code>.xls</code>, <code>.csv</code>
                        </li>
                        <li><i class="bi bi-hdd me-2" style="color:var(--ink-muted);"></i>
                            Ukuran file maksimal <strong>5 MB</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const fileInput    = document.getElementById('fileInput');
    const dropZone     = document.getElementById('dropZone');
    const filePreview  = document.getElementById('filePreview');
    const previewName  = document.getElementById('previewName');
    const previewSize  = document.getElementById('previewSize');
    const btnClear     = document.getElementById('btnClear');
    const btnImport    = document.getElementById('btnImport');
    const formImport   = document.getElementById('formImport');
    const progress     = document.getElementById('importProgress');
    const modeHidden   = document.getElementById('importModeHidden');

    const radioNew     = document.getElementById('modeNew');
    const radioUpdate  = document.getElementById('modeUpdate');
    const infoNew      = document.getElementById('infoModeNew');
    const infoUpdate   = document.getElementById('infoModeUpdate');
    const panduanNew   = document.getElementById('panduanNew');
    const panduanUpdate= document.getElementById('panduanUpdate');
    const btnTemplate  = document.getElementById('btnTemplate');
    const btnTemplateLabel = document.getElementById('btnTemplateLabel');

    // ── Mode switch ───────────────────────────────────────────────
    const btnGroupExport = document.getElementById('btnGroupExport');

    function applyMode(mode) {
        modeHidden.value = mode;
        const isUpdate   = mode === 'update';

        infoNew.classList.toggle('d-none', isUpdate);
        infoUpdate.classList.toggle('d-none', !isUpdate);
        panduanNew.classList.toggle('d-none', isUpdate);
        panduanUpdate.classList.toggle('d-none', !isUpdate);

        // Tampilkan tombol sesuai mode
        btnTemplate.classList.toggle('d-none', isUpdate);
        btnGroupExport.classList.toggle('d-none', !isUpdate);
    }

    radioNew.addEventListener('change',    () => applyMode('new'));
    radioUpdate.addEventListener('change', () => applyMode('update'));

    // ── File upload ───────────────────────────────────────────────
    function fmtSize(b) {
        if (b < 1024)    return b + ' B';
        if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
        return (b / 1048576).toFixed(1) + ' MB';
    }

    function showPreview(file) {
        previewName.textContent = file.name;
        previewSize.textContent = fmtSize(file.size);
        filePreview.classList.remove('d-none');
        dropZone.classList.add('d-none');
        btnImport.disabled = false;
    }

    function resetUpload() {
        fileInput.value = '';
        filePreview.classList.add('d-none');
        dropZone.classList.remove('d-none');
        btnImport.disabled = true;
    }

    fileInput.addEventListener('change', function () {
        if (this.files && this.files.length) showPreview(this.files[0]);
    });

    btnClear.addEventListener('click', resetUpload);

    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.style.background  = '#dbeafe';
        this.style.borderColor = '#1d4ed8';
    });

    dropZone.addEventListener('dragleave', function (e) {
        if (!this.contains(e.relatedTarget)) {
            this.style.background  = '#eff6ff';
            this.style.borderColor = '#93c5fd';
        }
    });

    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        this.style.background  = '#eff6ff';
        this.style.borderColor = '#93c5fd';
        const file = e.dataTransfer && e.dataTransfer.files[0];
        if (!file) return;
        try {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            fileInput.dispatchEvent(new Event('change'));
        } catch (_) {
            showPreview(file);
        }
    });

    formImport.addEventListener('submit', function (e) {
        if (!fileInput.files || !fileInput.files.length) { e.preventDefault(); return; }
        btnImport.disabled = true;
        btnImport.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
        progress.classList.remove('d-none');
    });
})();
</script>
@endpush