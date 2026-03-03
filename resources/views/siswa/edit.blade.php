{{-- resources/views/siswa/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Edit Siswa - ' . $siswa->nama)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.index') }}">Data Siswa</a></li>
    <li class="breadcrumb-item"><a href="{{ route('siswa.show', $siswa) }}">{{ $siswa->nama }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--navy); font-family:'Sora',sans-serif;">Edit Data Siswa</h4>
        <p class="mb-0" style="color:var(--ink-muted);font-size:.85rem;">
            <code style="font-size:.8rem;color:var(--navy);">{{ $siswa->id_siswa }}</code>
            <span class="mx-1">—</span>
            @php $jClass = 'badge-' . strtolower($siswa->jenjang); @endphp
            <span class="{{ $jClass }}">{{ $siswa->jenjang }}</span>
            Kelas {{ $siswa->kelas }}
        </p>
    </div>
    <a href="{{ route('siswa.show', $siswa) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row justify-content-center">
<div class="col-xl-8">

<form method="POST" action="{{ route('siswa.update', $siswa) }}">
@csrf
@method('PUT')

    {{-- ── Informasi Siswa ─────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header"
             style="background: var(--navy); color: #fff; border-radius: var(--r-xl) var(--r-xl) 0 0 !important;">
            <h6 class="mb-0 fw-bold" style="color:#fff;">
                <i class="bi bi-person-badge me-2"></i>Informasi Siswa
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">ID Siswa</label>
                    <input type="text"
                           class="form-control"
                           style="background:var(--bg);color:var(--ink-muted);"
                           value="{{ $siswa->id_siswa }}" readonly>
                    <div class="form-text">ID tidak dapat diubah.</div>
                </div>

                <div class="col-md-8">
                    <label class="form-label">
                        Nama Lengkap <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nama"
                           value="{{ old('nama', $siswa->nama) }}"
                           class="form-control @error('nama') is-invalid @enderror"
                           required>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Jenjang <span class="text-danger">*</span></label>
                    <select name="jenjang" id="selectJenjang"
                            class="form-select @error('jenjang') is-invalid @enderror"
                            {{ auth()->user()->jenjang ? 'disabled' : '' }} required>
                        <option value="TK"  {{ old('jenjang', $siswa->jenjang) == 'TK'  ? 'selected' : '' }}>TK / PAUD</option>
                        <option value="SD"  {{ old('jenjang', $siswa->jenjang) == 'SD'  ? 'selected' : '' }}>SD</option>
                        <option value="SMP" {{ old('jenjang', $siswa->jenjang) == 'SMP' ? 'selected' : '' }}>SMP</option>
                    </select>
                    @if(auth()->user()->jenjang)
                        <input type="hidden" name="jenjang" value="{{ $siswa->jenjang }}">
                    @endif
                    @error('jenjang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Kelas <span class="text-danger">*</span></label>
                    <select name="kelas" id="selectKelas"
                            class="form-select @error('kelas') is-invalid @enderror" required>
                        <option value="">— Pilih Kelas —</option>
                    </select>
                    @error('kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" id="selectStatus" class="form-select">
                        <option value="aktif"       {{ old('status', $siswa->status) == 'aktif'       ? 'selected' : '' }}>✅ Aktif</option>
                        <option value="tidak_aktif" {{ old('status', $siswa->status) == 'tidak_aktif' ? 'selected' : '' }}>⛔ Tidak Aktif</option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Nominal Pembayaran ────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                <i class="bi bi-cash-coin me-2"></i>Nominal Pembayaran
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">SPP / Bulan <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="nominal_pembayaran" id="inSPP"
                               value="{{ old('nominal_pembayaran', (int) $siswa->nominal_pembayaran) }}"
                               class="form-control @error('nominal_pembayaran') is-invalid @enderror"
                               min="0" step="1000" required>
                    </div>
                    @error('nominal_pembayaran')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Donatur / Bulan
                        <small style="color:var(--ink-muted);font-weight:400;">(pengurang)</small>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="nominal_donator" id="inDonatur"
                               value="{{ old('nominal_donator', (int) $siswa->nominal_donator) }}"
                               class="form-control" min="0" step="1000">
                    </div>
                </div>

                <div class="col-md-4" id="rowMamin"
                     style="{{ old('jenjang', $siswa->jenjang) === 'TK' ? '' : 'display:none' }}">
                    <label class="form-label">
                        Mamin / Bulan
                        <span class="badge ms-1"
                              style="background:var(--yellow-pale);color:#B45309;border:1px solid #FDE68A;
                                     font-size:.6rem;font-weight:600;">Khusus TK</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="nominal_mamin" id="inMamin"
                               value="{{ old('nominal_mamin', (int) $siswa->nominal_mamin) }}"
                               class="form-control" min="0" step="1000">
                    </div>
                </div>

                {{-- Preview tagihan --}}
                <div class="col-12">
                    <div class="rounded-3 p-3"
                         style="background:#f0fdf4; border:1px solid #bbf7d0;">
                        <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size:.85rem;">
                            <span style="color:var(--ink-muted);">SPP:</span>
                            <strong id="prevSPP" style="color:var(--navy);">Rp 0</strong>
                            <span style="color:var(--red);">−</span>
                            <span style="color:var(--ink-muted);">Donatur:</span>
                            <strong id="prevDonatur" style="color:var(--red);">Rp 0</strong>
                            <div id="prevMaminWrap"
                                 style="{{ old('jenjang', $siswa->jenjang) === 'TK' ? '' : 'display:none' }}"
                                 class="d-flex align-items-center gap-2">
                                <span style="color:var(--green);">+</span>
                                <span style="color:var(--ink-muted);">Mamin:</span>
                                <strong id="prevMamin" style="color:#0369a1;">Rp 0</strong>
                            </div>
                            <span style="color:var(--ink-muted);">=</span>
                            <span style="color:var(--ink-muted);">Tagihan/bln:</span>
                            <strong id="prevTotal" style="color:var(--green);font-size:1rem;">Rp 0</strong>
                        </div>
                        <div class="mt-1" style="font-size:.72rem; color:#166534;">
                            <i class="bi bi-info-circle me-1"></i>
                            Rumus: (SPP − Donatur + Mamin) × jumlah bulan
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Tanggal & Keterangan ─────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                <i class="bi bi-calendar3 me-2"></i>Tanggal & Keterangan
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_masuk"
                           value="{{ old('tanggal_masuk', $siswa->tanggal_masuk->format('Y-m-d')) }}"
                           class="form-control @error('tanggal_masuk') is-invalid @enderror" required>
                    @error('tanggal_masuk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tanggal Keluar</label>
                    <input type="date" name="tanggal_keluar" id="inputTanggalKeluar"
                           value="{{ old('tanggal_keluar', $siswa->tanggal_keluar?->format('Y-m-d')) }}"
                           class="form-control">
                    <div class="form-text">Kosongkan jika masih aktif.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan"
                           value="{{ old('keterangan', $siswa->keterangan) }}"
                           class="form-control" placeholder="Catatan (opsional)" maxlength="255">
                </div>

            </div>
        </div>
    </div>

    {{-- ── Tombol ──────────────────────────────────────────────── --}}
    <div class="d-flex gap-2 align-items-center">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-2"></i>Simpan Perubahan
        </button>
        <a href="{{ route('siswa.show', $siswa) }}" class="btn btn-outline-secondary">Batal</a>
        <div class="ms-auto">
            <button type="button" class="btn btn-outline-danger btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalHapus">
                <i class="bi bi-trash me-1"></i>Hapus Siswa
            </button>
        </div>
    </div>

</form>
</div>
</div>

{{-- Modal Hapus --}}
<div class="modal fade" id="modalHapus" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:48px;height:48px;background:#fee2e2;flex-shrink:0;">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                </div>
            </div>
            <div class="modal-body pt-2">
                <h5 class="fw-bold" style="color:var(--ink);">Hapus Siswa?</h5>
                <p style="color:var(--ink-muted);margin-bottom:0;">
                    Data siswa <strong style="color:var(--ink);">{{ $siswa->nama }}</strong>
                    ({{ $siswa->id_siswa }}) akan dihapus secara permanen dan tidak dapat dikembalikan.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('siswa.destroy', $siswa) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash me-1"></i>Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const kelasByJenjang = {
    TK:  ['KB', 'OA', 'OB'],
    SD:  ['I', 'II', 'III', 'IV', 'V', 'VI'],
    SMP: ['VII', 'VIII', 'IX'],
};
const currentKelas   = "{{ old('kelas', $siswa->kelas) }}";
const currentJenjang = "{{ old('jenjang', $siswa->jenjang) }}";

function renderKelas(jenjang, selected = '') {
    const sel = document.getElementById('selectKelas');
    sel.innerHTML = '<option value="">— Pilih Kelas —</option>';
    (kelasByJenjang[jenjang] || []).forEach(k => {
        sel.appendChild(new Option('Kelas ' + k, k, false, k === selected));
    });
}

function toggleMamin(jenjang) {
    const isTK = jenjang === 'TK';
    document.getElementById('rowMamin').style.display      = isTK ? '' : 'none';
    document.getElementById('prevMaminWrap').style.display = isTK ? '' : 'none';
    if (!isTK) document.getElementById('inMamin').value   = 0;
    updatePreview();
}

function updatePreview() {
    const spp   = parseFloat(document.getElementById('inSPP').value)    || 0;
    const donor = parseFloat(document.getElementById('inDonatur').value) || 0;
    const mamin = parseFloat(document.getElementById('inMamin').value)   || 0;
    const isTK  = document.getElementById('selectJenjang').value === 'TK';
    const total = spp - donor + (isTK ? mamin : 0);

    document.getElementById('prevSPP').textContent     = 'Rp ' + fmt(spp);
    document.getElementById('prevDonatur').textContent = 'Rp ' + fmt(donor);
    document.getElementById('prevMamin').textContent   = 'Rp ' + fmt(mamin);
    document.getElementById('prevTotal').textContent   = 'Rp ' + fmt(total);
}

function fmt(n) {
    return new Intl.NumberFormat('id-ID').format(Math.round(n));
}

document.getElementById('selectJenjang').addEventListener('change', function () {
    renderKelas(this.value);
    toggleMamin(this.value);
});

document.getElementById('selectStatus').addEventListener('change', function () {
    if (this.value === 'aktif') {
        document.getElementById('inputTanggalKeluar').value = '';
    }
});

['inSPP', 'inDonatur', 'inMamin'].forEach(id => {
    document.getElementById(id).addEventListener('input', updatePreview);
});

renderKelas(currentJenjang, currentKelas);
toggleMamin(currentJenjang);
updatePreview();
</script>
@endpush