{{-- resources/views/pembayaran/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Input Pembayaran')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('pembayaran.index') }}">Pembayaran</a></li>
    <li class="breadcrumb-item active">Input Baru</li>
@endsection

@push('styles')
<style>
    /* ── Tombol bulan ──────────────────────────────────────────── */
    .bulan-btn {
        font-size: .82rem;
        font-weight: 600;
        padding: .5rem .3rem;
        border-radius: var(--r-sm, .5rem);
        transition: all .15s;
        position: relative;
        line-height: 1.2;
    }
    .bulan-btn.belum {
        background: var(--surface);
        border: 1.5px solid var(--border);
        color: var(--ink-soft);
    }
    .bulan-btn.belum:hover {
        background: var(--blue-pale);
        border-color: var(--blue-light);
        color: var(--blue-dark);
    }
    .bulan-btn.selected {
        background: var(--navy);
        border: 1.5px solid var(--navy);
        color: #fff;
        box-shadow: 0 2px 8px rgba(12,30,62,.25);
    }
    .bulan-btn.selected::after {
        content: '✓';
        position: absolute;
        top: 2px; right: 5px;
        font-size: .6rem;
        color: rgba(255,255,255,.8);
    }
    .bulan-btn.dibayar {
        background: #dcfce7 !important;
        border: 1.5px solid #86efac !important;
        color: #15803d !important;
        cursor: not-allowed !important;
        pointer-events: none;
    }
    .bulan-btn.dibayar::after {
        content: '✓';
        position: absolute;
        top: 2px; right: 5px;
        font-size: .6rem;
        color: #16a34a;
    }
    .bulan-btn.tidak-aktif {
        background: var(--bg) !important;
        border: 1.5px solid var(--border) !important;
        color: var(--ink-faint) !important;
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
        <h4 class="fw-bold mb-1" style="color: var(--navy); font-family:'Sora',sans-serif;">
            Input Pembayaran
        </h4>
        <p class="mb-0" style="color:var(--ink-muted);font-size:.85rem;">Catat pembayaran SPP siswa</p>
    </div>
    <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

{{-- Error global --}}
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
    <div>
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('pembayaran.store') }}" id="formPembayaran">
@csrf

{{-- Hidden inputs bulan terpilih (diisi oleh JS) --}}
<div id="hiddenBulanInputs"></div>

<div class="row g-3">

    {{-- ══ KIRI ══════════════════════════════════════════════════════════ --}}
    <div class="col-md-7">

        {{-- Pilih Siswa --}}
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                    <i class="bi bi-person me-2" style="color:var(--blue);"></i>Pilih Siswa
                </h6>
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Siswa <span class="text-danger">*</span></label>
                    <select name="siswa_id" id="siswaSelect"
                            class="form-select @error('siswa_id') is-invalid @enderror" required>
                        <option value="">— Pilih Siswa —</option>
                        @foreach($daftarSiswa as $s)
                        <option value="{{ $s->id }}"
                            {{ (old('siswa_id', $siswa?->id) == $s->id) ? 'selected' : '' }}>
                            [{{ $s->jenjang }}-{{ $s->kelas }}] {{ $s->nama }}
                            @if($s->id_siswa) ({{ $s->id_siswa }}) @endif
                        </option>
                        @endforeach
                    </select>
                    @error('siswa_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Info siswa --}}
                <div id="infoSiswa" class="rounded-3 p-3 d-none"
                     style="background:var(--bg);border:1px solid var(--border);">
                    <div class="row g-2" style="font-size:.85rem;">
                        <div class="col-6">
                            <div style="color:var(--ink-muted);">Jenjang / Kelas</div>
                            <strong id="infoJenjang" style="color:var(--ink);">—</strong>
                        </div>
                        <div class="col-6">
                            <div style="color:var(--ink-muted);">SPP / bulan</div>
                            <strong id="infoNominal" style="color:var(--navy);">—</strong>
                        </div>
                        <div class="col-6">
                            <div style="color:var(--ink-muted);">Donatur / bulan</div>
                            <strong id="infoDonator" style="color:var(--red);">—</strong>
                        </div>
                        <div class="col-6" id="rowInfoMamin" style="display:none;">
                            <div style="color:var(--ink-muted);">Mamin / bulan</div>
                            <strong id="infoMamin" style="color:#0369a1;">—</strong>
                        </div>
                        <div class="col-6">
                            <div style="color:var(--ink-muted);">Saldo Kredit</div>
                            <strong id="infoKredit" style="color:var(--green);">—</strong>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-top d-flex justify-content-between"
                         style="font-size:.85rem;">
                        <span style="color:var(--ink-muted);">Tagihan / bulan:</span>
                        <strong id="infoTagihan" style="color:var(--green);">—</strong>
                    </div>
                </div>

                <div id="loadingSiswa" class="text-center py-2 d-none">
                    <div class="spinner-border spinner-border-sm me-2" style="color:var(--navy);"></div>
                    <span style="color:var(--ink-muted);font-size:.85rem;">Memuat data siswa...</span>
                </div>

                <div class="mt-3">
                    <label class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_bayar"
                           class="form-control @error('tanggal_bayar') is-invalid @enderror"
                           value="{{ old('tanggal_bayar', date('Y-m-d')) }}" required>
                    @error('tanggal_bayar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </div>

        {{-- Pilih Bulan --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                    <i class="bi bi-calendar3 me-2" style="color:var(--blue);"></i>Pilih Bulan
                </h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            id="btnSelectAll" disabled>Pilih Semua</button>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                            id="btnClearAll" disabled>Hapus Pilihan</button>
                </div>
            </div>
            <div class="card-body">

                @error('bulan_bayar')
                <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem;">
                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                </div>
                @enderror

                <div id="msgBulan" class="text-center py-3">
                    <i class="bi bi-arrow-up-circle d-block mb-2"
                       style="font-size:2rem;color:var(--ink-faint);"></i>
                    <span style="color:var(--ink-muted);font-size:.85rem;">Pilih siswa terlebih dahulu</span>
                </div>

                <div id="gridBulan" class="row g-2 d-none">
                    @php
                        $bulanList = [
                            '07' => 'Juli',     '08' => 'Agustus',  '09' => 'September',
                            '10' => 'Oktober',  '11' => 'November', '12' => 'Desember',
                            '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
                            '04' => 'April',    '05' => 'Mei',      '06' => 'Juni',
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
                    <div class="d-flex gap-3 flex-wrap" style="font-size:.75rem;color:var(--ink-muted);">
                        <span>
                            <span style="display:inline-block;width:12px;height:12px;
                                         background:#dcfce7;border:1px solid #86efac;border-radius:2px;"></span>
                            Sudah dibayar
                        </span>
                        <span>
                            <span style="display:inline-block;width:12px;height:12px;
                                         background:var(--surface);border:1.5px solid var(--border);border-radius:2px;"></span>
                            Belum
                        </span>
                        <span>
                            <span style="display:inline-block;width:12px;height:12px;
                                         background:var(--navy);border-radius:2px;"></span>
                            Dipilih
                        </span>
                        <span>
                            <span style="display:inline-block;width:12px;height:12px;
                                         background:var(--bg);border:1px solid var(--border);border-radius:2px;"></span>
                            Tidak aktif
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ══ KANAN: Ringkasan ══════════════════════════════════════════════════ --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"
                 style="background: var(--navy); border-radius: var(--r-xl) var(--r-xl) 0 0 !important;">
                <h6 class="mb-0 fw-bold" style="color:#fff;">
                    <i class="bi bi-receipt me-2"></i>Ringkasan Pembayaran
                </h6>
            </div>
            <div class="card-body">

                <table class="table table-sm mb-0" style="font-size:.865rem;">
                    <tr>
                        <td class="border-0 pb-2" style="color:var(--ink-muted);">Bulan Dipilih</td>
                        <td class="fw-bold text-end border-0 pb-2" style="color:var(--ink);">
                            <span id="jumlahBulan">0</span> bulan
                        </td>
                    </tr>
                    <tr>
                        <td class="pb-2" style="color:var(--ink-muted);">
                            SPP <small id="labelSPP" style="color:var(--ink-faint);">(Rp 0/bln)</small>
                        </td>
                        <td class="text-end pb-2" style="color:var(--ink-soft);">
                            <span id="subSPP">Rp 0</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="pb-2" style="color:var(--ink-muted);">
                            Donatur <small style="color:var(--ink-faint);">(pengurang)</small>
                        </td>
                        <td class="text-end pb-2">
                            <div class="input-group input-group-sm justify-content-end">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="nominal_donator" id="inputDonator"
                                       class="form-control text-end" value="0" min="0"
                                       style="max-width:110px;">
                            </div>
                            <div class="text-end mt-1" style="font-size:.8rem;color:var(--red);"
                                 id="subDonatur">−Rp 0</div>
                        </td>
                    </tr>
                    <tr id="rowSumMamin" class="d-none">
                        <td class="pb-2" style="color:var(--ink-muted);">
                            Mamin <small id="labelMamin" style="color:var(--ink-faint);">(Rp 0/bln)</small>
                        </td>
                        <td class="fw-600 text-end pb-2" style="color:#0369a1;">
                            <span id="subMamin">Rp 0</span>
                        </td>
                    </tr>
                    <tr id="rowSumKredit" class="d-none">
                        <td class="pb-2" style="color:var(--yellow);">Kredit Digunakan</td>
                        <td class="fw-600 text-end pb-2" style="color:var(--yellow);">
                            <span id="subKredit">−Rp 0</span>
                        </td>
                    </tr>
                    <tr style="border-top: 2px solid var(--border);">
                        <td class="fw-bold pt-2" style="color:var(--ink);">TOTAL</td>
                        <td class="fw-bold text-end pt-2" style="color:var(--green);font-size:1.1rem;">
                            <span id="grandTotal">Rp 0</span>
                        </td>
                    </tr>
                </table>

                {{-- Rumus helper --}}
                <div class="rounded-3 p-2 mt-2"
                     style="background:#f0fdf4;border:1px solid #bbf7d0;
                            font-size:.72rem;color:#166534;">
                    <i class="bi bi-info-circle me-1"></i>
                    Rumus: (SPP − Donatur + Mamin) × jumlah bulan − Kredit
                </div>

                <div class="mt-3">
                    <label class="form-label">Keterangan <small style="color:var(--ink-faint);font-weight:400;">(Opsional)</small></label>
                    <textarea name="keterangan" class="form-control form-control-sm" rows="2"
                              placeholder="Catatan pembayaran...">{{ old('keterangan') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3"
                        id="btnSubmit" disabled>
                    <i class="bi bi-save me-2"></i>Simpan Pembayaran
                </button>

                <div class="text-center mt-2" style="font-size:.82rem;color:var(--ink-muted);"
                     id="infoHelper">
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
// ─── State ────────────────────────────────────────────────────────────────────
let siswaData     = null;
let bulanDibayar  = [];
let bulanBelum    = [];
let bulanTerpilih = [];
let saldoKredit   = 0;
let tahunAjaran   = {{ $tahunAjaran }};

const SISWA_DATA_BASE = "{{ url('/siswa') }}";

// ─── Pilih Siswa ──────────────────────────────────────────────────────────────
document.getElementById('siswaSelect').addEventListener('change', async function () {
    const siswaId = this.value;
    if (!siswaId) { resetForm(); return; }

    document.getElementById('loadingSiswa').classList.remove('d-none');
    document.getElementById('infoSiswa').classList.add('d-none');
    document.getElementById('gridBulan').classList.add('d-none');
    document.getElementById('msgBulan').classList.add('d-none');
    document.getElementById('legendBulan').classList.add('d-none');

    try {
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
        saldoKredit  = json.saldo_kredit  ?? 0;

        const isTK          = siswaData.jenjang === 'TK';
        const spp           = parseFloat(siswaData.nominal_pembayaran) || 0;
        const donor         = parseFloat(siswaData.nominal_donator)    || 0;
        const mamin         = parseFloat(siswaData.nominal_mamin)      || 0;
        const tagihanPerBln = spp - donor + (isTK ? mamin : 0);

        document.getElementById('infoJenjang').textContent    = `${siswaData.jenjang} - Kelas ${siswaData.kelas}`;
        document.getElementById('infoNominal').textContent    = 'Rp ' + fmt(spp);
        document.getElementById('infoDonator').textContent    = 'Rp ' + fmt(donor);
        document.getElementById('infoMamin').textContent      = 'Rp ' + fmt(mamin);
        document.getElementById('infoTagihan').textContent    = 'Rp ' + fmt(tagihanPerBln) + '/bln';
        document.getElementById('infoKredit').textContent     = 'Rp ' + fmt(saldoKredit);
        document.getElementById('rowInfoMamin').style.display = isTK ? '' : 'none';
        document.getElementById('rowSumMamin').classList.toggle('d-none', !isTK);

        document.getElementById('inputDonator').value = donor;

        updateGridBulan();

        document.getElementById('infoSiswa').classList.remove('d-none');
        document.getElementById('gridBulan').classList.remove('d-none');
        document.getElementById('legendBulan').classList.remove('d-none');
        document.getElementById('btnSelectAll').disabled = bulanBelum.length === 0;
        document.getElementById('btnClearAll').disabled  = false;
        document.getElementById('msgBulan').classList.add('d-none');

    } catch (err) {
        document.getElementById('msgBulan').innerHTML =
            `<i class="bi bi-exclamation-triangle me-1" style="color:var(--red);"></i>
             <span style="color:var(--red);">${err.message}</span>`;
        document.getElementById('msgBulan').classList.remove('d-none');
    } finally {
        document.getElementById('loadingSiswa').classList.add('d-none');
    }

    updateRingkasan();
});

// ─── Render state tombol bulan ────────────────────────────────────────────────
function updateGridBulan() {
    bulanTerpilih = [];

    document.querySelectorAll('.bulan-btn').forEach(btn => {
        const bln    = btn.dataset.bulan;
        const tahun  = parseInt(bln) >= 7 ? tahunAjaran : tahunAjaran + 1;
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

// ─── Klik bulan (event delegation) ───────────────────────────────────────────
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

// ─── Pilih / Hapus Semua ─────────────────────────────────────────────────────
document.getElementById('btnSelectAll').addEventListener('click', function () {
    bulanTerpilih = [];
    document.querySelectorAll('.bulan-btn.belum').forEach(btn => {
        if (!btn.disabled) {
            btn.classList.replace('belum', 'selected');
            bulanTerpilih.push(btn.dataset.periode);
        }
    });
    updateHiddenInputs();
    updateRingkasan();
});

document.getElementById('btnClearAll').addEventListener('click', function () {
    bulanTerpilih = [];
    document.querySelectorAll('.bulan-btn.selected').forEach(btn => {
        btn.classList.replace('selected', 'belum');
    });
    updateHiddenInputs();
    updateRingkasan();
});

// ─── Hidden inputs bulan_bayar[] ─────────────────────────────────────────────
function updateHiddenInputs() {
    const container = document.getElementById('hiddenBulanInputs');
    container.innerHTML = '';
    bulanTerpilih.forEach(b => {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'bulan_bayar[]';
        inp.value = b;
        container.appendChild(inp);
    });
}

// ─── Update Ringkasan ─────────────────────────────────────────────────────────
document.getElementById('inputDonator').addEventListener('input', updateRingkasan);

function updateRingkasan() {
    if (!siswaData) {
        document.getElementById('btnSubmit').disabled = true;
        return;
    }

    const jml   = bulanTerpilih.length;
    const isTK  = siswaData.jenjang === 'TK';
    const spp   = parseFloat(siswaData.nominal_pembayaran) || 0;
    const mamin = isTK ? (parseFloat(siswaData.nominal_mamin) || 0) : 0;
    const donor = parseFloat(document.getElementById('inputDonator').value) || 0;

    const tagiBruto  = (spp - donor + mamin) * jml;
    const kredit     = Math.min(saldoKredit, tagiBruto);
    const totalBayar = Math.max(0, tagiBruto - kredit);

    document.getElementById('jumlahBulan').textContent = jml;
    document.getElementById('labelSPP').textContent    = `(Rp ${fmt(spp)}/bln)`;
    document.getElementById('subSPP').textContent      = 'Rp ' + fmt(spp * jml);
    document.getElementById('subDonatur').textContent  = '−Rp ' + fmt(donor * jml);
    document.getElementById('labelMamin').textContent  = `(Rp ${fmt(mamin)}/bln)`;
    document.getElementById('subMamin').textContent    = 'Rp ' + fmt(mamin * jml);
    document.getElementById('grandTotal').textContent  = 'Rp ' + fmt(totalBayar);

    const rowKredit = document.getElementById('rowSumKredit');
    if (kredit > 0) {
        document.getElementById('subKredit').textContent = '−Rp ' + fmt(kredit);
        rowKredit.classList.remove('d-none');
    } else {
        rowKredit.classList.add('d-none');
    }

    const btn    = document.getElementById('btnSubmit');
    const helper = document.getElementById('infoHelper');

    if (jml === 0) {
        btn.disabled       = true;
        helper.textContent = 'Pilih minimal 1 bulan untuk melanjutkan';
        helper.style.color = 'var(--yellow)';
    } else {
        btn.disabled       = false;
        helper.textContent = `${jml} bulan dipilih — Bayar: Rp ${fmt(totalBayar)}`;
        helper.style.color = 'var(--green)';
        helper.style.fontWeight = '600';
    }
}

// ─── Reset ────────────────────────────────────────────────────────────────────
function resetForm() {
    siswaData = null; bulanDibayar = []; bulanBelum = [];
    bulanTerpilih = []; saldoKredit = 0;

    ['infoSiswa', 'gridBulan', 'legendBulan'].forEach(id => {
        document.getElementById(id).classList.add('d-none');
    });

    document.getElementById('msgBulan').classList.remove('d-none');
    document.getElementById('hiddenBulanInputs').innerHTML = '';
    document.getElementById('btnSelectAll').disabled = true;
    document.getElementById('btnClearAll').disabled  = true;
    document.getElementById('inputDonator').value    = 0;
    document.getElementById('rowSumKredit').classList.add('d-none');

    updateRingkasan();
}

function fmt(n) {
    return new Intl.NumberFormat('id-ID').format(Math.round(parseFloat(n) || 0));
}

// Jika siswa sudah di-preselect dari query string, trigger change
@if($siswa)
    document.addEventListener('DOMContentLoaded', () => {
        const sel = document.getElementById('siswaSelect');
        if (sel.value) sel.dispatchEvent(new Event('change'));
    });
@endif
</script>
@endpush