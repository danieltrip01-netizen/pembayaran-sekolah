{{-- resources/views/siswa/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Tambah Siswa')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.index') }}">Data Siswa</a></li>
    <li class="breadcrumb-item active">Tambah Siswa</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">Tambah Siswa Baru</h4>
        <p class="text-muted small mb-0">Daftarkan siswa baru ke sistem</p>
    </div>
    <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row justify-content-center">
<div class="col-xl-8">

<form method="POST" action="{{ route('siswa.store') }}" id="formSiswa">
@csrf

    {{-- Informasi Dasar --}}
    <div class="card mb-3">
        <div class="card-header py-3" style="background:var(--primary);color:white">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-person-badge me-2"></i>Informasi Siswa
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">

                {{-- ID Siswa --}}
                <div class="col-md-4">
                    <label class="form-label fw-600 small">ID Siswa</label>
                    <div class="input-group">
                        <input type="text" name="id_siswa" id="idSiswa"
                               value="{{ old('id_siswa', $idSiswa) }}"
                               class="form-control @error('id_siswa') is-invalid @enderror"
                               placeholder="Auto">
                        <button type="button" class="btn btn-outline-secondary" id="btnRegenId" title="Generate ulang">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                    </div>
                    @error('id_siswa')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Kosongkan untuk auto-generate.</div>
                </div>

                {{-- Nama --}}
                <div class="col-md-8">
                    <label class="form-label fw-600 small">
                        Nama Lengkap <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nama" value="{{ old('nama') }}"
                           class="form-control @error('nama') is-invalid @enderror"
                           placeholder="Nama lengkap siswa" required autofocus>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Jenjang --}}
                <div class="col-md-4">
                    <label class="form-label fw-600 small">
                        Jenjang <span class="text-danger">*</span>
                    </label>
                    <select name="jenjang" id="selectJenjang"
                            class="form-select @error('jenjang') is-invalid @enderror"
                            {{ auth()->user()->jenjang ? 'disabled' : '' }} required>
                        @if(auth()->user()->jenjang)
                            <option value="{{ auth()->user()->jenjang }}" selected>
                                {{ auth()->user()->jenjang }}
                            </option>
                        @else
                            <option value="">-- Pilih --</option>
                            <option value="TK"  {{ old('jenjang', $jenjang) == 'TK'  ? 'selected' : '' }}>TK / PAUD</option>
                            <option value="SD"  {{ old('jenjang', $jenjang) == 'SD'  ? 'selected' : '' }}>SD</option>
                            <option value="SMP" {{ old('jenjang', $jenjang) == 'SMP' ? 'selected' : '' }}>SMP</option>
                        @endif
                    </select>
                    {{-- Jika disabled, tetap kirim value --}}
                    @if(auth()->user()->jenjang)
                        <input type="hidden" name="jenjang" value="{{ auth()->user()->jenjang }}">
                    @endif
                    @error('jenjang')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Kelas --}}
                <div class="col-md-4">
                    <label class="form-label fw-600 small">
                        Kelas <span class="text-danger">*</span>
                    </label>
                    <select name="kelas" id="selectKelas"
                            class="form-select @error('kelas') is-invalid @enderror" required>
                        <option value="">-- Pilih Kelas --</option>
                    </select>
                    @error('kelas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="col-md-4">
                    <label class="form-label fw-600 small">Status</label>
                    <select name="status" class="form-select">
                        <option value="aktif"       {{ old('status', 'aktif') == 'aktif'       ? 'selected' : '' }}>Aktif</option>
                        <option value="tidak_aktif" {{ old('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    {{-- Nominal & Tanggal --}}
    <div class="card mb-3">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold" style="color:var(--primary)">
                <i class="bi bi-cash-coin me-2"></i>Nominal Pembayaran
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">

                {{-- SPP --}}
                <div class="col-md-4">
                    <label class="form-label fw-600 small">
                        SPP / Bulan <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text text-muted">Rp</span>
                        <input type="number" name="nominal_pembayaran"
                               value="{{ old('nominal_pembayaran', 0) }}"
                               class="form-control @error('nominal_pembayaran') is-invalid @enderror"
                               min="0" required>
                    </div>
                    @error('nominal_pembayaran')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Donatur --}}
                <div class="col-md-4">
                    <label class="form-label fw-600 small">Donatur / Bulan</label>
                    <div class="input-group">
                        <span class="input-group-text text-muted">Rp</span>
                        <input type="number" name="nominal_donator"
                               value="{{ old('nominal_donator', 0) }}"
                               class="form-control" min="0">
                    </div>
                </div>

                {{-- Mamin (hanya TK) --}}
                <div class="col-md-4" id="rowMamin"
                     style="{{ old('jenjang', $jenjang) === 'TK' ? '' : 'display:none' }}">
                    <label class="form-label fw-600 small">
                        Mamin / Bulan
                        <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">Khusus TK</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text text-muted">Rp</span>
                        <input type="number" name="nominal_mamin" id="inputMamin"
                               value="{{ old('nominal_mamin', 0) }}"
                               class="form-control" min="0">
                    </div>
                </div>

                {{-- Preview Total --}}
                <div class="col-12">
                    <div class="rounded-3 p-3" style="background:#f0f4f8;border:1px solid #e2e8f0">
                        <div class="d-flex gap-4 flex-wrap small">
                            <div>
                                <span class="text-muted">SPP:</span>
                                <strong id="prevSPP" class="text-primary ms-1">Rp 0</strong>
                            </div>
                            <div>
                                <span class="text-muted">Donatur:</span>
                                <strong id="prevDonatur" class="ms-1">Rp 0</strong>
                            </div>
                            <div id="prevMaminWrap" style="{{ old('jenjang', $jenjang) === 'TK' ? '' : 'display:none' }}">
                                <span class="text-muted">Mamin:</span>
                                <strong id="prevMamin" class="text-info ms-1">Rp 0</strong>
                            </div>
                            <div>
                                <span class="text-muted">Total Tagihan/bln:</span>
                                <strong id="prevTotal" class="text-success ms-1">Rp 0</strong>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Tanggal --}}
    <div class="card mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold" style="color:var(--primary)">
                <i class="bi bi-calendar3 me-2"></i>Tanggal
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label fw-600 small">
                        Tanggal Masuk <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="tanggal_masuk"
                           value="{{ old('tanggal_masuk', date('Y') . '-07-01') }}"
                           class="form-control @error('tanggal_masuk') is-invalid @enderror" required>
                    @error('tanggal_masuk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Default: 1 Juli tahun ini.</div>
                </div>

                <div class="col-md-4" id="rowTanggalKeluar">
                    <label class="form-label fw-600 small">Tanggal Keluar</label>
                    <input type="date" name="tanggal_keluar"
                           value="{{ old('tanggal_keluar') }}"
                           class="form-control @error('tanggal_keluar') is-invalid @enderror">
                    @error('tanggal_keluar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Kosongkan jika siswa masih aktif.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-600 small">Keterangan</label>
                    <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                           class="form-control" placeholder="Catatan (opsional)" maxlength="255">
                </div>

            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-2"></i>Simpan Siswa
        </button>
        <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary">
            Batal
        </a>
        <button type="reset" class="btn btn-outline-secondary ms-auto">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Form
        </button>
    </div>

</form>
</div>
</div>

@endsection

@push('scripts')
<script>
// ── Kelas per Jenjang ───────────────────────────────────────────
const kelasByJenjang = {
    TK:  ['A', 'B'],
    SD:  ['I', 'II', 'III', 'IV', 'V', 'VI'],
    SMP: ['VII', 'VIII', 'IX'],
};

const oldKelas   = "{{ old('kelas') }}";
const oldJenjang = "{{ old('jenjang', $jenjang) }}";

function updateKelas(jenjang, selectedKelas = '') {
    const sel = document.getElementById('selectKelas');
    sel.innerHTML = '<option value="">-- Pilih Kelas --</option>';
    (kelasByJenjang[jenjang] || []).forEach(k => {
        const opt = document.createElement('option');
        opt.value    = k;
        opt.textContent = 'Kelas ' + k;
        if (k === selectedKelas) opt.selected = true;
        sel.appendChild(opt);
    });
}

function toggleMamin(jenjang) {
    const show = jenjang === 'TK';
    document.getElementById('rowMamin').style.display      = show ? '' : 'none';
    document.getElementById('prevMaminWrap').style.display = show ? '' : 'none';
    if (!show) {
        document.getElementById('inputMamin').value = 0;
    }
    updatePreview();
}

// Init
updateKelas(oldJenjang, oldKelas);
toggleMamin(oldJenjang);

document.getElementById('selectJenjang')?.addEventListener('change', function() {
    updateKelas(this.value);
    toggleMamin(this.value);
    // Generate ulang ID saat jenjang berubah
    genId(this.value);
});

// ── Preview Total ────────────────────────────────────────────────
function updatePreview() {
    const spp     = parseFloat(document.querySelector('[name="nominal_pembayaran"]').value) || 0;
    const donatur = parseFloat(document.querySelector('[name="nominal_donator"]').value) || 0;
    const mamin   = parseFloat(document.getElementById('inputMamin').value) || 0;
    const total   = spp + donatur + mamin;

    document.getElementById('prevSPP').textContent    = 'Rp ' + fmt(spp);
    document.getElementById('prevDonatur').textContent = 'Rp ' + fmt(donatur);
    document.getElementById('prevMamin').textContent   = 'Rp ' + fmt(mamin);
    document.getElementById('prevTotal').textContent   = 'Rp ' + fmt(total);
}

document.querySelectorAll('[name="nominal_pembayaran"],[name="nominal_donator"]')
    .forEach(el => el.addEventListener('input', updatePreview));
document.getElementById('inputMamin').addEventListener('input', updatePreview);
updatePreview();

// ── Generate ID ──────────────────────────────────────────────────
async function genId(jenjang) {
    if (!jenjang) return;
    // Hit AJAX endpoint untuk generate ID baru
    try {
        const res  = await fetch(`/siswa/generate-id?jenjang=${jenjang}`);
        if (res.ok) {
            const data = await res.json();
            document.getElementById('idSiswa').value = data.id_siswa;
        }
    } catch(e) { /* silent */ }
}

document.getElementById('btnRegenId').addEventListener('click', function() {
    const jenjang = document.getElementById('selectJenjang')?.value
                 || "{{ auth()->user()->jenjang ?? 'SD' }}";
    genId(jenjang);
});

function fmt(n) {
    return new Intl.NumberFormat('id-ID').format(n);
}
</script>
@endpush