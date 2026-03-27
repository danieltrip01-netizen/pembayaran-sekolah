{{-- resources/views/pembayaran/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Edit Pembayaran')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('pembayaran.index') }}">Pembayaran</a></li>
    <li class="breadcrumb-item">
        <a href="{{ route('pembayaran.show', $pembayaran) }}">{{ $pembayaran->kode_bayar }}</a>
    </li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@push('styles')
<style>
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
    .bulan-btn.belum:focus,
    .bulan-btn.belum:active {
        background: var(--surface) !important;
        border-color: var(--border) !important;
        color: var(--ink-soft) !important;
        box-shadow: none !important;
        outline: none;
    }
    .bulan-btn.belum:focus:hover,
    .bulan-btn.belum:active:hover {
        background: var(--blue-pale) !important;
        border-color: var(--blue-light) !important;
        color: var(--blue-dark) !important;
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

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--navy); font-family:'Sora',sans-serif;">
            Edit Pembayaran
        </h4>
        <p class="mb-0" style="color:var(--ink-muted);font-size:.85rem;">
            <code style="color:var(--navy);font-size:.8rem;">{{ $pembayaran->kode_bayar }}</code>
            <span class="mx-1" style="color:var(--ink-faint);">·</span>
            <span style="color:var(--ink-soft);">{{ $pembayaran->siswa->nama ?? '—' }}</span>
        </p>
    </div>
    <a href="{{ route('pembayaran.show', $pembayaran) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <div>
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('pembayaran.update', $pembayaran) }}" id="formEdit">
@csrf
@method('PUT')
<div id="hiddenBulanInputs"></div>

<div class="row g-3">

    {{-- ══ KIRI: Info siswa + Grid bulan ═══════════════════════════════════ --}}
    <div class="col-md-7">

        {{-- Info siswa (readonly) --}}
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                    <i class="bi bi-person-circle me-2" style="color:var(--blue);"></i>Informasi Siswa
                </h6>
            </div>
            <div class="card-body py-3">
                <div class="row g-2" style="font-size:.875rem;">
                    <div class="col-6">
                        <div style="color:var(--ink-muted);font-size:.75rem;font-weight:600;
                                    text-transform:uppercase;letter-spacing:.4px;">Nama</div>
                        <div class="fw-600 mt-1" style="color:var(--ink);">
                            {{ $pembayaran->siswa->nama ?? '—' }}
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="color:var(--ink-muted);font-size:.75rem;font-weight:600;
                                    text-transform:uppercase;letter-spacing:.4px;">Kelas</div>
                        <div class="fw-600 mt-1" style="color:var(--ink);">
                            {{ $pembayaran->siswa->kelasAktif?->kelas?->nama ?? '—' }}
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="color:var(--ink-muted);font-size:.75rem;font-weight:600;
                                    text-transform:uppercase;letter-spacing:.4px;">SPP/bln</div>
                        <div class="fw-600 mt-1" style="color:var(--navy);">
                            Rp {{ number_format($pembayaran->nominal_per_bulan, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="color:var(--ink-muted);font-size:.75rem;font-weight:600;
                                    text-transform:uppercase;letter-spacing:.4px;">Donatur/bln</div>
                        <div class="fw-600 mt-1" style="color:var(--red);">
                            Rp {{ number_format($donaturPerBln, 0, ',', '.') }}
                        </div>
                    </div>
                    @if($maminPerBln > 0)
                    <div class="col-4">
                        <div style="color:var(--ink-muted);font-size:.75rem;font-weight:600;
                                    text-transform:uppercase;letter-spacing:.4px;">Mamin/bln</div>
                        <div class="fw-600 mt-1" style="color:#0369a1;">
                            Rp {{ number_format($maminPerBln, 0, ',', '.') }}
                        </div>
                    </div>
                    @endif
                    <div class="col-12">
                        <div class="mt-1 pt-2 border-top d-flex justify-content-between"
                             style="font-size:.85rem;">
                            <span style="color:var(--ink-muted);">Tagihan/bulan:</span>
                            <strong style="color:var(--green);">
                                Rp {{ number_format($pembayaran->nominal_per_bulan - $donaturPerBln + $maminPerBln, 0, ',', '.') }}/bln
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grid Pilih Bulan --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                    <i class="bi bi-calendar3 me-2" style="color:var(--blue);"></i>Pilih Bulan
                </h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            id="btnSelectAll">Pilih Semua</button>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                            id="btnClearAll">Hapus Pilihan</button>
                </div>
            </div>
            <div class="card-body">

                @error('bulan_bayar')
                <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem;">
                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                </div>
                @enderror

                <div id="gridBulan" class="row g-2">
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

                <div class="mt-3 pt-2 border-top">
                    <div class="d-flex gap-3 flex-wrap" style="font-size:.75rem;color:var(--ink-muted);">
                        <span>
                            <span style="display:inline-block;width:12px;height:12px;
                                         background:var(--navy);border-radius:2px;"></span>
                            Dipilih
                        </span>
                        <span>
                            <span style="display:inline-block;width:12px;height:12px;
                                         background:var(--surface);border:1.5px solid var(--border);border-radius:2px;"></span>
                            Tersedia
                        </span>
                        <span>
                            <span style="display:inline-block;width:12px;height:12px;
                                         background:#dcfce7;border:1px solid #86efac;border-radius:2px;"></span>
                            Sudah dibayar
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

    {{-- ══ KANAN: Ringkasan + Form fields ══════════════════════════════════ --}}
    <div class="col-md-5">

        {{-- Ringkasan live --}}
        <div class="card mb-3">
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
                            SPP <small style="color:var(--ink-faint);">
                                (Rp {{ number_format($pembayaran->nominal_per_bulan, 0, ',', '.') }}/bln)
                            </small>
                        </td>
                        <td class="text-end pb-2" style="color:var(--ink-soft);">
                            <span id="subSPP">Rp 0</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="pb-2" style="color:var(--ink-muted);">
                            Donatur <small style="color:var(--ink-faint);">(pengurang)</small>
                        </td>
                        <td class="text-end pb-2" style="color:var(--red);">
                            <span id="subDonatur">−Rp 0</span>
                        </td>
                    </tr>
                    @if($maminPerBln > 0)
                    <tr>
                        <td class="pb-2" style="color:var(--ink-muted);">Mamin</td>
                        <td class="fw-600 text-end pb-2" style="color:#0369a1;">
                            <span id="subMamin">Rp 0</span>
                        </td>
                    </tr>
                    @endif
                    @if(($pembayaran->kredit_digunakan ?? 0) > 0)
                    <tr>
                        <td class="pb-2" style="color:var(--yellow);">Kredit</td>
                        <td class="fw-600 text-end pb-2" style="color:var(--yellow);">
                            −Rp {{ number_format($pembayaran->kredit_digunakan, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                    <tr style="border-top: 2px solid var(--border);">
                        <td class="fw-bold pt-2" style="color:var(--ink);">TOTAL</td>
                        <td class="fw-bold text-end pt-2" style="color:var(--green);font-size:1.1rem;">
                            <span id="grandTotal">Rp 0</span>
                        </td>
                    </tr>
                </table>

            </div>
        </div>

        {{-- Form fields --}}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                    <i class="bi bi-pencil me-2" style="color:var(--blue);"></i>Edit Data
                </h6>
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">
                        Tanggal Bayar <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="tanggal_bayar"
                           value="{{ old('tanggal_bayar', $pembayaran->tanggal_bayar->format('Y-m-d')) }}"
                           class="form-control @error('tanggal_bayar') is-invalid @enderror" required>
                    @error('tanggal_bayar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        Keterangan
                        <small style="color:var(--ink-faint);font-weight:400;">(Opsional)</small>
                    </label>
                    <textarea name="keterangan" class="form-control" rows="2"
                              placeholder="Catatan (opsional)">{{ old('keterangan', $pembayaran->keterangan) }}</textarea>
                </div>

                <div id="alertMinBulan" class="alert alert-warning py-2 mb-3 d-none"
                     style="font-size:.82rem;">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Pilih minimal 1 bulan untuk menyimpan.
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4" id="btnSimpan" disabled>
                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                    </button>
                    <a href="{{ route('pembayaran.show', $pembayaran) }}"
                       class="btn btn-outline-secondary">Batal</a>
                </div>

            </div>
        </div>
    </div>

</div>
</form>
@endsection

@push('scripts')
<script>
const TAHUN_AJARAN      = {{ $tahunAjaran }};
const SPP_PER_BULAN     = {{ (int) $pembayaran->nominal_per_bulan }};
const DONATUR_PER_BULAN = {{ $donaturPerBln }};
const MAMIN_PER_BULAN   = {{ $maminPerBln }};
const KREDIT            = {{ (int) ($pembayaran->kredit_digunakan ?? 0) }};

// Bulan milik pembayaran ini (pre-selected)
const BULAN_TERPILIH_INI  = @json($bulanTerpilih);
// Bulan yang dikunci karena milik transaksi lain
const BULAN_DIBAYAR_LAIN  = @json($bulanDibayarLain);
// Semua periode aktif tahun pelajaran siswa ini
const BULAN_AKTIF         = @json($bulanAktif);

let bulanTerpilih = [];

// ── Init grid ─────────────────────────────────────────────────────────────────
function initGrid() {
    document.querySelectorAll('.bulan-btn').forEach(btn => {
        const bln    = btn.dataset.bulan;
        const tahun  = parseInt(bln) >= 7 ? TAHUN_AJARAN : TAHUN_AJARAN + 1;
        const periode = `${String(tahun).padStart(4, '0')}-${bln}`;
        btn.dataset.periode = periode;

        if (BULAN_DIBAYAR_LAIN.includes(periode)) {
            btn.className = 'btn bulan-btn w-100 dibayar';
            btn.disabled  = true;
            btn.title     = '✓ Dibayar di transaksi lain';
        } else if (BULAN_AKTIF.includes(periode)) {
            if (BULAN_TERPILIH_INI.includes(periode)) {
                btn.className = 'btn bulan-btn w-100 selected';
                btn.disabled  = false;
                bulanTerpilih.push(periode);
            } else {
                btn.className = 'btn bulan-btn w-100 belum';
                btn.disabled  = false;
            }
            btn.title = 'Klik untuk memilih/batal';
        } else {
            btn.className = 'btn bulan-btn w-100 tidak-aktif';
            btn.disabled  = true;
            btn.title     = 'Di luar periode aktif';
        }
    });

    updateHiddenInputs();
    updateRingkasan();
}

// ── Klik bulan ────────────────────────────────────────────────────────────────
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

// ── Pilih Semua ───────────────────────────────────────────────────────────────
document.getElementById('btnSelectAll').addEventListener('click', function () {
    bulanTerpilih = [];
    document.querySelectorAll('.bulan-btn:not(.dibayar):not(.tidak-aktif)').forEach(btn => {
        if (!btn.disabled) {
            btn.classList.remove('belum');
            btn.classList.add('selected');
            if (btn.dataset.periode) bulanTerpilih.push(btn.dataset.periode);
        }
    });
    updateHiddenInputs();
    updateRingkasan();
});

// ── Hapus Pilihan ─────────────────────────────────────────────────────────────
document.getElementById('btnClearAll').addEventListener('click', function () {
    bulanTerpilih = [];
    document.querySelectorAll('.bulan-btn.selected').forEach(btn => {
        btn.classList.replace('selected', 'belum');
    });
    updateHiddenInputs();
    updateRingkasan();
});

// ── Hidden inputs ─────────────────────────────────────────────────────────────
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

// ── Ringkasan live ────────────────────────────────────────────────────────────
function updateRingkasan() {
    const jml        = bulanTerpilih.length;
    const tagiBruto  = (SPP_PER_BULAN - DONATUR_PER_BULAN + MAMIN_PER_BULAN) * jml;
    const totalBayar = Math.max(0, tagiBruto - KREDIT);

    document.getElementById('jumlahBulan').textContent = jml;
    document.getElementById('subSPP').textContent      = 'Rp ' + fmt(SPP_PER_BULAN * jml);
    document.getElementById('subDonatur').textContent  = '−Rp ' + fmt(DONATUR_PER_BULAN * jml);
    const elMamin = document.getElementById('subMamin');
    if (elMamin) elMamin.textContent = 'Rp ' + fmt(MAMIN_PER_BULAN * jml);
    document.getElementById('grandTotal').textContent  = 'Rp ' + fmt(totalBayar);

    const btnSimpan   = document.getElementById('btnSimpan');
    const alertMinBln = document.getElementById('alertMinBulan');

    if (jml === 0) {
        btnSimpan.disabled = true;
        alertMinBln.classList.remove('d-none');
    } else {
        btnSimpan.disabled = false;
        alertMinBln.classList.add('d-none');
    }
}

function fmt(n) {
    return new Intl.NumberFormat('id-ID').format(Math.round(parseFloat(n) || 0));
}

document.addEventListener('DOMContentLoaded', initGrid);
</script>
@endpush