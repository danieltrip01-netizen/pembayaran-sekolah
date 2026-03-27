{{-- resources/views/setting/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Data Sekolah')

@section('breadcrumb')
    <li class="breadcrumb-item active">Data Sekolah</li>
@endsection

@push('styles')
<style>
    .upload-zone {
        border: 2px dashed #93c5fd;
        background: #eff6ff;
        border-radius: .75rem;
        transition: all .2s;
        cursor: pointer;
    }
    .upload-zone:hover, .upload-zone.dragover { background: #dbeafe; border-color: #1d4ed8; }
    .upload-zone.has-file { border-color: #6ee7b7; background: #f0fdf4; }

    .preview-wrap { position: relative; display: inline-block; }
    .preview-wrap img {
        border-radius: .5rem;
        border: 1px solid #e2e8f0;
        object-fit: contain;
        background: #f8fafc;
    }
    .btn-remove-img {
        position: absolute; top: -8px; right: -8px;
        width: 24px; height: 24px; border-radius: 50%;
        background: #ef4444; color: white; border: 2px solid white;
        font-size: .65rem; display: flex; align-items: center;
        justify-content: center; cursor: pointer;
        box-shadow: 0 1px 4px rgba(0,0,0,.2);
    }
    .section-label {
        font-size: .68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: #94a3b8; margin-bottom: .75rem;
    }
    .nav-tabs-setting .nav-link {
        font-weight: 600; font-size: .85rem; color: #64748b;
        border-radius: .5rem .5rem 0 0;
        border: 1px solid transparent;
        padding: .6rem 1.25rem;
    }
    .nav-tabs-setting .nav-link.active {
        color: var(--primary);
        background: #fff;
        border-color: #e2e8f0 #e2e8f0 #fff;
    }
    .nav-tabs-setting .nav-link:not(.active):hover { background: #f8fafc; }
    .tab-jenjang-badge {
        font-size: .65rem; padding: .15rem .45rem;
        border-radius: .3rem; margin-left: .3rem;
    }
    .doc-preview {
        border: 1px solid #e2e8f0;
        border-radius: .5rem;
        background: #fdfdfd;
        font-size: .78rem;
        padding: 1.25rem;
    }
</style>
@endpush

@section('content')

@php
    $activeTab = request('tab', $userJenjang ?? 'TK');
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color:var(--primary)">Data Sekolah</h4>
        <p class="text-muted small mb-0">
            @if($userJenjang)
                Identitas, kepala sekolah, dan tanda tangan
            @else
                Data lengkap masing-masing sekolah: TK, SD, dan SMP
            @endif
        </p>
    </div>
</div>

{{-- Alert error --}}
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Terdapat kesalahan:</strong>
    <ul class="mb-0 mt-1 ps-3 small">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════
     ADMIN JENJANG: Tampilkan satu form langsung tanpa tab
═══════════════════════════════════════════════════════════════════════ --}}
@if($userJenjang)
    @include('setting._form_jenjang', [
        'setting'        => $setting,
        'jenjang'        => $userJenjang,
        'isAdminYayasan' => false,
    ])

{{-- ══════════════════════════════════════════════════════════════════════
     ADMIN YAYASAN: Tab TK + SD + SMP (masing-masing data lengkap)
═══════════════════════════════════════════════════════════════════════ --}}
@else
    {{-- Tab navigation --}}
    <ul class="nav nav-tabs nav-tabs-setting border-bottom mb-0" id="settingTabs">
        @php
            $tabConfig = [
                'TK'  => ['label' => 'TK',  'icon' => 'bi-stars',       'color' => '#db2777', 'badge' => 'background:#fce7f3;color:#db2777;border:1px solid #f9a8d4'],
                'SD'  => ['label' => 'SD',  'icon' => 'bi-mortarboard', 'color' => '#1d4ed8', 'badge' => 'background:#dbeafe;color:#1d4ed8;border:1px solid #93c5fd'],
                'SMP' => ['label' => 'SMP', 'icon' => 'bi-award',       'color' => '#059669', 'badge' => 'background:#d1fae5;color:#059669;border:1px solid #6ee7b7'],
            ];
        @endphp
        @foreach($tabConfig as $key => $cfg)
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === $key ? 'active' : '' }}"
               href="{{ route('setting.index', ['tab' => $key]) }}">
                <i class="bi {{ $cfg['icon'] }} me-1" style="color:{{ $cfg['color'] }}"></i>
                {{ $cfg['label'] }}
                <span class="tab-jenjang-badge" style="{{ $cfg['badge'] }}">{{ $key }}</span>
            </a>
        </li>
        @endforeach
    </ul>

    {{-- Tab content --}}
    <div class="tab-content border border-top-0 rounded-bottom bg-white p-4 shadow-sm">
        @if(in_array($activeTab, ['TK', 'SD', 'SMP']))
            @include('setting._form_jenjang', [
                'setting'        => $settings[$activeTab],
                'jenjang'        => $activeTab,
                'isAdminYayasan' => true,
            ])
        @endif
    </div>
@endif

@endsection
@push('scripts')
<script>
// ── Reusable upload setup ──────────────────────────────────────────────
function setupUpload(cfg) {
    const { inputId, dropZoneId, labelId, newPreviewId, newImgId, newNameId, btnBatalId,
            hapusInputId, btnHapusCurrId, currWrapId, livePreviewId, livePreviewHeight } = cfg;

    const input      = document.getElementById(inputId);
    const dropZone   = document.getElementById(dropZoneId);
    if (!input || !dropZone) return;

    const label      = document.getElementById(labelId);
    const newPreview = document.getElementById(newPreviewId);
    const newImg     = document.getElementById(newImgId);
    const newName    = document.getElementById(newNameId);
    const btnBatal   = document.getElementById(btnBatalId);
    const hapusInput = document.getElementById(hapusInputId);
    const btnHapus   = document.getElementById(btnHapusCurrId);
    const currWrap   = document.getElementById(currWrapId);

    function applyFile(file) {
        if (!file || !file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = e => {
            if (newImg) newImg.src = e.target.result;
            if (newPreview) newPreview.classList.remove('d-none');
            if (newName) newName.textContent = file.name;
            dropZone.classList.add('has-file');
            if (label) label.textContent = 'File dipilih — klik untuk mengganti';

            if (livePreviewId) {
                const lp = document.getElementById(livePreviewId);
                if (lp) {
                    if (lp.tagName === 'IMG') {
                        lp.src = e.target.result;
                    } else {
                        const img = document.createElement('img');
                        img.id = livePreviewId;
                        img.height = livePreviewHeight || 50;
                        img.alt = 'Preview';
                        img.src = e.target.result;
                        if (livePreviewHeight === 42) img.style.margin = '2px 0';
                        lp.replaceWith(img);
                    }
                }
            }
        };
        reader.readAsDataURL(file);
    }

    input.addEventListener('change', () => { if (input.files[0]) applyFile(input.files[0]); });

    ['dragover','dragleave','drop'].forEach(ev => dropZone.addEventListener(ev, e => e.preventDefault()));
    dropZone.addEventListener('dragover',  () => dropZone.classList.add('dragover'));
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', e => {
        dropZone.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer(); dt.items.add(file);
            input.files = dt.files; applyFile(file);
        }
    });

    btnBatal?.addEventListener('click', () => {
        input.value = '';
        if (newPreview) newPreview.classList.add('d-none');
        if (newImg) newImg.src = '';
        dropZone.classList.remove('has-file');
        if (label) label.textContent = 'Pilih atau seret file';
    });

    btnHapus?.addEventListener('click', () => {
        if (hapusInput) hapusInput.value = '1';
        if (currWrap)   currWrap.style.display = 'none';
    });
}

document.addEventListener('DOMContentLoaded', function () {

    ['logo', 'ttd'].forEach(key => {
        setupUpload({
            inputId:        key + 'Input',
            dropZoneId:     key + 'DropZone',
            labelId:        key + 'Label',
            newPreviewId:   key + 'NewPreview',
            newImgId:       key + 'NewImg',
            newNameId:      key + 'NewName',
            btnBatalId:     'btnBatal_' + key,
            hapusInputId:   'hapus_' + key,
            btnHapusCurrId: 'btnHapus_' + key,
            currWrapId:     key + 'CurrWrap',
            livePreviewId:  key === 'logo' ? 'previewLogoImg' : 'previewTtdImg',
            livePreviewHeight: key === 'logo' ? 52 : 42,
        });
    });

    // Live preview teks
    const liveMap = {
        'nama_sekolah'        : ['previewNamaSekolah', '[ Nama Sekolah ]'],
        'nama_yayasan'        : ['previewNamaYayasan', '[ Nama Yayasan ]'],
        'nama_kepala_sekolah' : ['previewKepala',      '( Nama Kepala Sekolah )'],
        'nama_admin'          : ['previewAdmin',        '( Nama Admin )'],
    };

    Object.entries(liveMap).forEach(([name, cfg]) => {
        const input = document.querySelector(`[name="${name}"]`);
        if (!input || !cfg) return;
        input.addEventListener('input', () => {
            const el = document.getElementById(cfg[0]);
            if (el) el.textContent = input.value.trim() || cfg[1];
        });
    });

    // Alamat + kota
    ['alamat','kota'].forEach(n => {
        document.querySelector(`[name="${n}"]`)?.addEventListener('input', () => {
            const a = document.querySelector('[name="alamat"]')?.value.trim() || '';
            const k = document.querySelector('[name="kota"]')?.value.trim() || '';
            const el = document.getElementById('previewAlamat');
            if (el) el.textContent = [a, k].filter(Boolean).join(', ') || '[ Alamat Sekolah ]';
        });
    });

    document.querySelector('[name="telepon"]')?.addEventListener('input', function () {
        const el = document.getElementById('previewTelepon');
        if (el) el.textContent = this.value.trim() ? 'Telp. ' + this.value.trim() : '';
    });
});
</script>
@endpush