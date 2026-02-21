{{-- resources/views/siswa/import.blade.php --}}
@extends('layouts.app')
@section('title', 'Import Data Siswa')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.index') }}">Data Siswa</a></li>
    <li class="breadcrumb-item active">Import Excel</li>
@endsection

@push('styles')
<style>
    /* Animasi masuk untuk kartu hasil import */
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-18px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Glow sukses / gagal sesaat setelah muncul */
    @keyframes glowSuccess {
        0%   { box-shadow: 0 0 0 0 rgba(5, 150, 105, .35); }
        60%  { box-shadow: 0 0 0 10px rgba(5, 150, 105, 0); }
        100% { box-shadow: 0 0 0 0 rgba(5, 150, 105, 0); }
    }
    @keyframes glowDanger {
        0%   { box-shadow: 0 0 0 0 rgba(220, 38, 38, .35); }
        60%  { box-shadow: 0 0 0 10px rgba(220, 38, 38, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
    }

    #importResult {
        animation: slideInDown .35s ease both;
    }
    #importResult.glow-success {
        animation: slideInDown .35s ease both, glowSuccess .8s ease .4s both;
    }
    #importResult.glow-danger {
        animation: slideInDown .35s ease both, glowDanger .8s ease .4s both;
    }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">Import Data Siswa</h4>
        <p class="text-muted small mb-0">Upload file Excel untuk menambah data siswa secara massal</p>
    </div>
    <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

{{-- ═══ HASIL IMPORT ═══ --}}
@if(session('import_summary'))
    @php
        $okCount  = session('import_ok', 0);
        $failList = session('import_failures') ?? [];
        $isOk     = $okCount > 0;
    @endphp

    <div id="importResult"
         class="card mb-4 border-0 shadow-sm {{ $isOk ? 'glow-success' : 'glow-danger' }}"
         style="border-left: 4px solid {{ $isOk ? '#059669' : '#dc2626' }} !important">
        <div class="card-body">
            <div class="d-flex align-items-start gap-3">

                {{-- Ikon status --}}
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:42px;height:42px;background:{{ $isOk ? '#d1fae5' : '#fee2e2' }}">
                    <i class="bi {{ $isOk ? 'bi-check-lg text-success' : 'bi-exclamation-triangle text-danger' }} fs-5"></i>
                </div>

                <div class="flex-grow-1">
                    <div class="fw-bold mb-1">Laporan Hasil Import</div>
                    <p class="mb-0 small text-dark">{!! session('import_summary') !!}</p>

                    {{-- Ringkasan angka --}}
                    <div class="d-flex gap-3 mt-2">
                        @if($okCount > 0)
                        <span class="small">
                            <span class="badge rounded-pill"
                                  style="background:#d1fae5;color:#059669;border:1px solid #6ee7b7">
                                <i class="bi bi-check2 me-1"></i>{{ $okCount }} berhasil
                            </span>
                        </span>
                        @endif
                        @if(count($failList) > 0)
                        <span class="small">
                            <span class="badge rounded-pill"
                                  style="background:#fee2e2;color:#dc2626;border:1px solid #fca5a5">
                                <i class="bi bi-x me-1"></i>{{ count($failList) }} gagal
                            </span>
                        </span>
                        @endif
                    </div>
                </div>

                <a href="{{ route('siswa.index') }}" class="btn btn-sm btn-outline-primary shadow-sm flex-shrink-0">
                    <i class="bi bi-list-ul me-1"></i>Daftar Siswa
                </a>
            </div>

            {{-- Detail baris bermasalah (collapsible) --}}
            @if(count($failList) > 0)
            <div class="mt-3">
                <button class="btn btn-sm btn-outline-danger" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseErrors">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Lihat {{ count($failList) }} Baris yang Bermasalah
                </button>
                <div class="collapse mt-2" id="collapseErrors">
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-sm mb-0" style="font-size:.82rem">
                            <thead style="background:#fef2f2">
                                <tr>
                                    <th style="width:100px" class="ps-3">Baris</th>
                                    <th style="width:200px">Nama di File</th>
                                    <th>Keterangan Masalah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($failList as $err)
                                <tr>
                                    <td class="ps-3 text-danger fw-bold">Baris {{ $err['row'] }}</td>
                                    <td>{{ $err['nama'] }}</td>
                                    <td class="text-danger small">{{ $err['pesan'] }}</td>
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

@if(session('error'))
<div class="alert alert-danger border-0 shadow-sm mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
</div>
@endif

{{-- ═══ FORM & PANDUAN ═══ --}}
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header py-3" style="background:var(--primary);color:white">
                <h6 class="mb-0 fw-bold"><i class="bi bi-upload me-2"></i>Unggah File Excel</h6>
            </div>
            <div class="card-body p-4">

                @if($jenjang)
                <div class="alert border-0 rounded-3 mb-4 py-2 px-3 small"
                     style="background:#eff6ff;color:#1d4ed8">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Sistem mendeteksi Anda sebagai admin <strong>{{ $jenjang }}</strong>.
                    Data jenjang lain akan otomatis dilewati.
                </div>
                @endif

                <form method="POST" action="{{ route('siswa.import.store') }}"
                      enctype="multipart/form-data" id="formImport">
                    @csrf

                    {{-- Drop zone --}}
                    <div id="dropZone"
                         class="rounded-3 text-center py-5 px-4 mb-0"
                         style="border:2px dashed #93c5fd;background:#eff6ff;transition:all .2s ease-in-out;cursor:pointer">
                        <i class="bi bi-file-earmark-excel-fill fs-1 mb-3 d-block" style="color:#1d4ed8"></i>
                        <div class="fw-bold mb-1" style="color:#1d4ed8">Tarik file Excel ke sini</div>
                        <div class="text-muted small mb-4">Format: .xlsx, .xls, .csv &nbsp;·&nbsp; Maks. 5 MB</div>
                        <label for="fileInput" class="btn btn-primary px-4 shadow-sm" style="cursor:pointer">
                            <i class="bi bi-folder2-open me-2"></i>Pilih File dari Komputer
                        </label>
                        <input type="file" id="fileInput" name="file"
                               accept=".xlsx,.xls,.csv" class="d-none">
                    </div>

                    {{-- Preview file terpilih --}}
                    <div id="filePreview" class="d-none mt-4">
                        <div class="rounded-3 p-3 d-flex align-items-center gap-3"
                             style="background:#f0fdf4;border:1px solid #bbf7d0">
                            <i class="bi bi-file-earmark-check-fill text-success fs-3"></i>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-bold small text-truncate text-success" id="previewName">File terpilih</div>
                                <div class="text-muted" style="font-size:.75rem" id="previewSize">0 KB</div>
                            </div>
                            <button type="button" id="btnClear"
                                    class="btn btn-sm btn-link text-danger text-decoration-none">
                                <i class="bi bi-trash3 me-1"></i>Hapus
                            </button>
                        </div>
                    </div>

                    @error('file')
                    <div class="alert alert-danger py-2 px-3 small mt-3 border-0">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                    @enderror

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success px-4 shadow-sm"
                                id="btnImport" disabled>
                            <i class="bi bi-cloud-arrow-up me-2"></i>Mulai Import Sekarang
                        </button>
                        <a href="{{ route('siswa.import.template') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-download me-1"></i>Template Excel
                        </a>
                    </div>
                </form>

                {{-- Progress bar saat upload --}}
                <div id="importProgress" class="d-none mt-4">
                    <div class="text-primary small mb-2 fw-bold">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Sedang memproses data, jangan tutup halaman ini...
                    </div>
                    <div class="progress" style="height:10px">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary w-100"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-5">
        {{-- Aturan kolom --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold" style="color:var(--primary)">
                    <i class="bi bi-info-square me-2"></i>Aturan Pengisian Kolom
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="font-size:.8rem">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Nama Kolom</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $wajib = '<span class="badge bg-danger-subtle text-danger" style="font-size:.65rem">Wajib</span>';
                            $opsi  = '<span class="badge bg-secondary-subtle text-secondary" style="font-size:.65rem">Opsional</span>';
                            $kolom = [
                                ['nama',               $wajib, 'Nama lengkap siswa sesuai akta.'],
                                ['kelas',              $wajib, 'Gunakan angka Romawi (I, II, dst) untuk SD/SMP.'],
                                ['jenjang',            $wajib, 'Isi: <code>TK</code>, <code>SD</code>, atau <code>SMP</code>.'],
                                ['nominal_pembayaran', $opsi,  'Hanya angka tanpa titik/koma.'],
                                ['nominal_donator',    $opsi,  'Gunakan 0 jika tidak ada keringanan.'],
                            ];
                            @endphp
                            @foreach($kolom as [$c, $b, $k])
                            <tr>
                                <td class="ps-3"><code>{{ $c }}</code></td>
                                <td>{!! $b !!}</td>
                                <td class="text-muted small">{!! $k !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tips --}}
        <div class="card shadow-sm border-0">
            <div class="card-body bg-light rounded-3 p-3">
                <h6 class="fw-bold small mb-2">
                    <i class="bi bi-lightbulb-fill text-warning me-2"></i>Tips Cepat
                </h6>
                <ul class="small mb-0 ps-3 text-muted" style="line-height:1.8">
                    <li>ID Siswa akan dibuat otomatis oleh sistem.</li>
                    <li>Siswa baru otomatis berstatus <strong>Aktif</strong>.</li>
                    <li>Pastikan baris pertama Excel adalah <strong>header</strong> (nama kolom).</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Auto-scroll + highlight ke hasil import ──────────────────────
    const resultCard = document.getElementById('importResult');
    if (resultCard) {
        // Beri jeda singkat agar animasi CSS slide-in terasa
        setTimeout(() => {
            resultCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 120);
    }

    // ── File input & drop zone ────────────────────────────────────────
    const fileInput  = document.getElementById('fileInput');
    const dropZone   = document.getElementById('dropZone');
    const filePreview = document.getElementById('filePreview');
    const previewName = document.getElementById('previewName');
    const previewSize = document.getElementById('previewSize');
    const btnClear   = document.getElementById('btnClear');
    const btnImport  = document.getElementById('btnImport');
    const formImport = document.getElementById('formImport');
    const progress   = document.getElementById('importProgress');

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const sizes = ['B', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return parseFloat((bytes / Math.pow(1024, i)).toFixed(1)) + ' ' + sizes[i];
    }

    function applyFile(file) {
        if (!file) return;
        previewName.textContent = file.name;
        previewSize.textContent = formatBytes(file.size);
        filePreview.classList.remove('d-none');
        dropZone.classList.add('d-none');
        btnImport.disabled = false;
    }

    fileInput.addEventListener('change', function () {
        if (this.files?.[0]) applyFile(this.files[0]);
    });

    btnClear.addEventListener('click', function () {
        fileInput.value = '';
        filePreview.classList.add('d-none');
        dropZone.classList.remove('d-none');
        btnImport.disabled = true;
    });

    // Drag & drop
    ['dragover', 'dragleave', 'drop'].forEach(ev =>
        dropZone.addEventListener(ev, e => e.preventDefault())
    );
    dropZone.addEventListener('dragover', () => {
        dropZone.style.background    = '#dbeafe';
        dropZone.style.borderColor   = '#1d4ed8';
    });
    ['dragleave', 'drop'].forEach(ev =>
        dropZone.addEventListener(ev, () => {
            dropZone.style.background  = '#eff6ff';
            dropZone.style.borderColor = '#93c5fd';
        })
    );
    dropZone.addEventListener('drop', e => {
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            applyFile(file);
        }
    });

    // ── Submit: tampilkan progress ────────────────────────────────────
    formImport.addEventListener('submit', function () {
        btnImport.disabled    = true;
        btnImport.innerHTML   = '<span class="spinner-border spinner-border-sm me-2"></span>Sedang Mengunggah...';
        progress.classList.remove('d-none');
    });

});
</script>
@endpush