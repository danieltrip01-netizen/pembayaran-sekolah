{{-- resources/views/pembayaran/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Input Pembayaran')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('pembayaran.index') }}">Pembayaran</a></li>
    <li class="breadcrumb-item active">Input Baru</li>
@endsection

@push('styles')
<style>
.bulan-btn {
    font-size: .82rem;
    font-weight: 600;
    padding: .45rem .3rem;
    border-radius: .5rem;
    transition: all .15s;
    position: relative;
}
.bulan-btn.belum {
    background: #fff;
    border: 1.5px solid #94a3b8;
    color: #475569;
}
.bulan-btn.belum:hover {
    background: #f1f5f9;
    border-color: #64748b;
}
.bulan-btn.selected {
    background: var(--primary, #1B4B8A);
    border: 1.5px solid var(--primary, #1B4B8A);
    color: #fff;
    box-shadow: 0 2px 8px rgba(27,75,138,.3);
}
.bulan-btn.selected::after {
    content: '✓';
    position: absolute;
    top: 2px; right: 5px;
    font-size: .65rem;
}
.bulan-btn.dibayar {
    background: #dcfce7 !important;
    border: 1.5px solid #86efac !important;
    color: #15803d !important;
    opacity: 1 !important;
    cursor: not-allowed !important;
    pointer-events: none;
}
.bulan-btn.dibayar::after {
    content: '✓';
    position: absolute;
    top: 2px; right: 5px;
    font-size: .65rem;
    color: #16a34a;
}
.bulan-btn.tidak-aktif {
    background: #f8fafc !important;
    border: 1.5px solid #e2e8f0 !important;
    color: #cbd5e1 !important;
    opacity: 1 !important;
    cursor: not-allowed !important;
    pointer-events: none;
}
</style>
@endpush

@section('content')
<div class="row justify-content-center">
<div class="col-xl-9">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">Input Pembayaran</h4>
        <p class="text-muted small mb-0">Catat pembayaran SPP siswa</p>
    </div>
    <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@if($errors->has('bulan_bayar'))
<div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first('bulan_bayar') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('pembayaran.store') }}" id="formPembayaran">
@csrf

<div class="row g-3">

    {{-- ══ KIRI ══════════════════════════════════════════════════ --}}
    <div class="col-md-7">

        {{-- Pilih Siswa --}}
        <div class="card mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-person me-2" style="color:var(--primary)"></i>Pilih Siswa
                </h6>
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label fw-600">Siswa <span class="text-danger">*</span></label>
                    <select name="siswa_id" id="siswaSelect" class="form-select" required>
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($siswaList as $s)
                        <option value="{{ $s->id }}"
                            {{ (old('siswa_id', $selectedSiswa?->id) == $s->id) ? 'selected' : '' }}>
                            [{{ $s->jenjang }}-{{ $s->kelas }}] {{ $s->nama }} ({{ $s->id_siswa }})
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Info siswa --}}
                <div id="infoSiswa" class="rounded-3 p-3 d-none"
                     style="background:#f8fafc;border:1px solid #e2e8f0">
                    <div class="row g-2 small">
                        <div class="col-6">
                            <div class="text-muted">Jenjang / Kelas</div>
                            <strong id="infoJenjang">-</strong>
                        </div>
                        <div class="col-6">
                            <div class="text-muted">SPP / bulan</div>
                            <strong id="infoNominal" class="text-primary">-</strong>
                        </div>
                        <div class="col-6">
                            <div class="text-muted">Donatur / bulan</div>
                            <strong id="infoDonator">-</strong>
                        </div>
                        <div class="col-6" id="rowInfoMamin" style="display:none">
                            <div class="text-muted">Mamin / bulan</div>
                            <strong id="infoMamin" class="text-info">-</strong>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-top d-flex justify-content-between small">
                        <span class="text-muted">Tagihan / bulan:</span>
                        <strong id="infoTagihan" class="text-success">-</strong>
                    </div>
                </div>

                <div id="loadingSiswa" class="text-center py-2 d-none">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                    <span class="small text-muted">Memuat data siswa...</span>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-600">Tanggal Bayar <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_bayar" class="form-control"
                           value="{{ old('tanggal_bayar', date('Y-m-d')) }}" required>
                </div>

            </div>
        </div>

        {{-- Pilih Bulan --}}
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-calendar3 me-2" style="color:var(--primary)"></i>Pilih Bulan
                </h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            id="btnSelectAll" disabled>Pilih Semua</button>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                            id="btnClearAll" disabled>Hapus Pilihan</button>
                </div>
            </div>
            <div class="card-body">

                <div id="msgBulan" class="text-center py-3 text-muted small">
                    <i class="bi bi-arrow-up-circle fs-3 d-block mb-2 text-primary opacity-50"></i>
                    Pilih siswa terlebih dahulu
                </div>

                <div id="gridBulan" class="row g-2 d-none">
                    @php
                        $bulanList = [
                            '07'=>'Juli','08'=>'Agustus','09'=>'September',
                            '10'=>'Oktober','11'=>'November','12'=>'Desember',
                            '01'=>'Januari','02'=>'Februari','03'=>'Maret',
                            '04'=>'April','05'=>'Mei','06'=>'Juni',
                        ];
                    @endphp
                    @foreach($bulanList as $bln => $nama)
                    <div class="col-3">
                        <button type="button"
                                class="btn bulan-btn w-100 tidak-aktif"
                                data-bulan="{{ $bln }}"
                                disabled>
                            {{ substr($nama, 0, 3) }}
                        </button>
                    </div>
                    @endforeach
                </div>

                <div id="legendBulan" class="d-none mt-3 pt-2 border-top">
                    <div class="d-flex gap-3 flex-wrap" style="font-size:.75rem;color:#64748b">
                        <span><span style="display:inline-block;width:12px;height:12px;background:#dcfce7;border:1px solid #86efac;border-radius:2px"></span> Sudah dibayar</span>
                        <span><span style="display:inline-block;width:12px;height:12px;background:#fff;border:1.5px solid #94a3b8;border-radius:2px"></span> Belum</span>
                        <span><span style="display:inline-block;width:12px;height:12px;background:var(--primary);border-radius:2px"></span> Dipilih</span>
                        <span><span style="display:inline-block;width:12px;height:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:2px"></span> Tidak aktif</span>
                    </div>
                </div>

                <div id="hiddenBulanInputs"></div>
            </div>
        </div>
    </div>

    {{-- ══ KANAN: Ringkasan ═══════════════════════════════════════ --}}
    <div class="col-md-5">
        <div class="card sticky-top" style="top: 80px">
            <div class="card-header py-3" style="background: var(--primary); color:white">
                <h6 class="mb-0 fw-bold"><i class="bi bi-receipt me-2"></i>Ringkasan Pembayaran</h6>
            </div>
            <div class="card-body">

                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted border-0">Bulan Dipilih</td>
                        <td class="fw-bold text-end border-0">
                            <span id="jumlahBulan">0</span> bulan
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">SPP <small id="labelSPP" class="text-muted">(Rp 0/bln)</small></td>
                        <td class="text-end"><span id="subSPP">Rp 0</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">
                            Donatur <small class="text-muted">(pengurang)</small>
                        </td>
                        <td class="text-end">
                            <div class="input-group input-group-sm justify-content-end">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="nominal_donator" id="inputDonator"
                                       class="form-control text-end" value="0" min="0"
                                       style="max-width:110px">
                            </div>
                            <div class="text-danger small text-end mt-1" id="subDonatur">- Rp 0</div>
                        </td>
                    </tr>
                    <tr id="rowSumMamin" class="d-none">
                        <td class="text-muted">Mamin <small id="labelMamin" class="text-muted">(Rp 0/bln)</small></td>
                        <td class="fw-bold text-end text-info"><span id="subMamin">Rp 0</span></td>
                    </tr>
                    <tr style="border-top: 2px solid #e2e8f0">
                        <td class="fw-bold">TOTAL</td>
                        <td class="fw-bold text-end text-primary fs-5">
                            <span id="grandTotal">Rp 0</span>
                        </td>
                    </tr>
                </table>

                {{-- Rumus helper --}}
                <div class="rounded-3 p-2 mt-2" style="background:#f0fdf4;border:1px solid #bbf7d0;font-size:.72rem;color:#166534">
                    <i class="bi bi-info-circle me-1"></i>
                    Rumus: (SPP − Donatur + Mamin) × jumlah bulan
                </div>

                <div class="mt-3">
                    <label class="form-label small fw-600">Keterangan (Opsional)</label>
                    <textarea name="keterangan" class="form-control form-control-sm" rows="2"
                              placeholder="Catatan pembayaran...">{{ old('keterangan') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3"
                        id="btnSubmit" disabled>
                    <i class="bi bi-save me-2"></i>Simpan Pembayaran
                </button>

                <div class="text-center mt-2 small text-muted" id="infoHelper">
                    Pilih siswa dan bulan untuk melanjutkan
                </div>

            </div>
        </div>
    </div>
</div>
</form>
</div>
</div>
@endsection

@push('scripts')
<script>
// ─── State ───────────────────────────────────────────────────────
let siswaData     = null;
let bulanDibayar  = [];
let bulanBelum    = [];
let bulanTerpilih = [];
let tahunAjaran   = {{ $tahunAjaran }};

// ✅ FIX: Gunakan url() helper dari Blade agar URL selalu benar
//         meski app dipasang di subdirektori
const SISWA_DATA_BASE = "{{ url('/siswa') }}";

// ─── Pilih Siswa ─────────────────────────────────────────────────
document.getElementById('siswaSelect').addEventListener('change', async function () {
    const siswaId = this.value;
    if (!siswaId) { resetForm(); return; }

    document.getElementById('loadingSiswa').classList.remove('d-none');
    document.getElementById('infoSiswa').classList.add('d-none');
    document.getElementById('gridBulan').classList.add('d-none');
    document.getElementById('msgBulan').classList.add('d-none');
    document.getElementById('legendBulan').classList.add('d-none');

    try {
        // ✅ FIX: Gunakan SISWA_DATA_BASE dari url() Blade, tambah header Accept: application/json
        //         agar Laravel selalu mengembalikan JSON (bukan redirect/HTML saat error)
        const res = await fetch(`${SISWA_DATA_BASE}/${siswaId}/data`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        if (!res.ok) {
            const msg = res.status === 404 ? 'Data siswa tidak ditemukan.'
                      : res.status === 403 ? 'Akses tidak diizinkan.'
                      : `Gagal mengambil data siswa (HTTP ${res.status}).`;
            throw new Error(msg);
        }
        const json = await res.json();

        siswaData    = json.siswa;
        bulanDibayar = json.bulan_dibayar ?? [];
        bulanBelum   = json.bulan_belum   ?? [];

        const isTK   = siswaData.jenjang === 'TK';
        const spp    = parseFloat(siswaData.nominal_pembayaran) || 0;
        const donor  = parseFloat(siswaData.nominal_donator)    || 0;
        const mamin  = parseFloat(siswaData.nominal_mamin)      || 0;

        // ✅ RUMUS: (SPP - Donatur + Mamin) × 1 untuk preview per bulan
        const tagihanPerBln = spp - donor + (isTK ? mamin : 0);

        document.getElementById('infoJenjang').textContent    = `${siswaData.jenjang} - Kelas ${siswaData.kelas}`;
        document.getElementById('infoNominal').textContent    = 'Rp ' + fmt(spp);
        document.getElementById('infoDonator').textContent    = 'Rp ' + fmt(donor);
        document.getElementById('infoMamin').textContent      = 'Rp ' + fmt(mamin);
        document.getElementById('infoTagihan').textContent    = 'Rp ' + fmt(tagihanPerBln) + '/bln';
        document.getElementById('rowInfoMamin').style.display = isTK ? '' : 'none';
        document.getElementById('rowSumMamin').classList.toggle('d-none', !isTK);

        // Set donatur default dari data siswa
        document.getElementById('inputDonator').value = donor;

        updateGridBulan();

        document.getElementById('infoSiswa').classList.remove('d-none');
        document.getElementById('gridBulan').classList.remove('d-none');
        document.getElementById('legendBulan').classList.remove('d-none');
        document.getElementById('btnSelectAll').disabled = bulanBelum.length === 0;
        document.getElementById('btnClearAll').disabled  = false;

    } catch (err) {
        document.getElementById('msgBulan').innerHTML =
            `<i class="bi bi-exclamation-triangle text-danger me-1"></i>${err.message}`;
        document.getElementById('msgBulan').classList.remove('d-none');
    } finally {
        document.getElementById('loadingSiswa').classList.add('d-none');
    }

    updateRingkasan();
});

// ─── Render state tombol bulan ───────────────────────────────────
function updateGridBulan() {
    bulanTerpilih = [];

    document.querySelectorAll('.bulan-btn').forEach(btn => {
        const bln     = btn.dataset.bulan;
        const tahun   = parseInt(bln) >= 7 ? tahunAjaran : tahunAjaran + 1;
        const periode = `${String(tahun).padStart(4,'0')}-${bln}`;
        btn.dataset.periode = periode;

        if (bulanDibayar.includes(periode)) {
            btn.className = 'btn bulan-btn w-100 dibayar';
            btn.disabled  = true;
            btn.title     = '✓ Sudah dibayar';
        } else if (bulanBelum.includes(periode)) {
            btn.className = 'btn bulan-btn w-100 belum';
            btn.disabled  = false;
            btn.title     = 'Klik untuk memilih';
        } else {
            btn.className = 'btn bulan-btn w-100 tidak-aktif';
            btn.disabled  = true;
            btn.title     = 'Di luar periode aktif';
        }
    });
}

// ─── Klik bulan (event delegation) ──────────────────────────────
document.getElementById('gridBulan').addEventListener('click', function (e) {
    const btn = e.target.closest('.bulan-btn');
    if (!btn || btn.classList.contains('dibayar') || btn.classList.contains('tidak-aktif') || btn.disabled) return;

    const periode = btn.dataset.periode;
    if (!periode) return;

    if (btn.classList.contains('selected')) {
        btn.classList.remove('selected');
        btn.classList.add('belum');
        bulanTerpilih = bulanTerpilih.filter(b => b !== periode);
    } else {
        btn.classList.remove('belum');
        btn.classList.add('selected');
        bulanTerpilih.push(periode);
    }
    updateHiddenInputs();
    updateRingkasan();
});

// ─── Pilih / Hapus Semua ─────────────────────────────────────────
document.getElementById('btnSelectAll').addEventListener('click', function () {
    bulanTerpilih = [];
    document.querySelectorAll('.bulan-btn.belum').forEach(btn => {
        if (!btn.disabled) {
            btn.classList.replace('belum', 'selected');
            bulanTerpilih.push(btn.dataset.periode);
        }
    });
    updateHiddenInputs(); updateRingkasan();
});

document.getElementById('btnClearAll').addEventListener('click', function () {
    bulanTerpilih = [];
    document.querySelectorAll('.bulan-btn.selected').forEach(btn => btn.classList.replace('selected', 'belum'));
    updateHiddenInputs(); updateRingkasan();
});

// ─── Hidden inputs ───────────────────────────────────────────────
function updateHiddenInputs() {
    const c = document.getElementById('hiddenBulanInputs');
    c.innerHTML = '';
    bulanTerpilih.forEach(b => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'bulan_bayar[]'; inp.value = b;
        c.appendChild(inp);
    });
}

// ─── Update Ringkasan ────────────────────────────────────────────
document.getElementById('inputDonator').addEventListener('input', updateRingkasan);

function updateRingkasan() {
    if (!siswaData) { document.getElementById('btnSubmit').disabled = true; return; }

    const jml   = bulanTerpilih.length;
    const isTK  = siswaData.jenjang === 'TK';
    const spp   = parseFloat(siswaData.nominal_pembayaran) || 0;
    const mamin = isTK ? (parseFloat(siswaData.nominal_mamin) || 0) : 0;
    const donor = parseFloat(document.getElementById('inputDonator').value) || 0;

    // ✅ RUMUS BENAR: (SPP - Donatur + Mamin) × jumlah_bulan
    const total = (spp - donor + mamin) * jml;

    document.getElementById('jumlahBulan').textContent  = jml;
    document.getElementById('labelSPP').textContent     = `(Rp ${fmt(spp)}/bln)`;
    document.getElementById('subSPP').textContent       = 'Rp ' + fmt(spp * jml);
    document.getElementById('subDonatur').textContent   = '− Rp ' + fmt(donor * jml);
    document.getElementById('labelMamin').textContent   = `(Rp ${fmt(mamin)}/bln)`;
    document.getElementById('subMamin').textContent     = 'Rp ' + fmt(mamin * jml);
    document.getElementById('grandTotal').textContent   = 'Rp ' + fmt(total);

    const btn    = document.getElementById('btnSubmit');
    const helper = document.getElementById('infoHelper');

    if (jml === 0) {
        btn.disabled      = true;
        helper.textContent = 'Pilih minimal 1 bulan untuk melanjutkan';
        helper.className   = 'text-center mt-2 small text-warning';
    } else {
        btn.disabled      = false;
        helper.textContent = `${jml} bulan dipilih — Total: Rp ${fmt(total)}`;
        helper.className   = 'text-center mt-2 small text-success fw-600';
    }
}

// ─── Reset ───────────────────────────────────────────────────────
function resetForm() {
    siswaData = null; bulanDibayar = []; bulanBelum = []; bulanTerpilih = [];
    ['infoSiswa','gridBulan','legendBulan'].forEach(id => document.getElementById(id).classList.add('d-none'));
    document.getElementById('msgBulan').classList.remove('d-none');
    document.getElementById('hiddenBulanInputs').innerHTML = '';
    document.getElementById('btnSelectAll').disabled = true;
    document.getElementById('btnClearAll').disabled  = true;
    document.getElementById('inputDonator').value    = 0;
    updateRingkasan();
}

function fmt(n) {
    return new Intl.NumberFormat('id-ID').format(Math.round(parseFloat(n) || 0));
}

@if($selectedSiswa)
    document.addEventListener('DOMContentLoaded', () => {
        const sel = document.getElementById('siswaSelect');
        if (sel.value) sel.dispatchEvent(new Event('change'));
    });
@endif
</script>
@endpush