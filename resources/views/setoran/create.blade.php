{{-- resources/views/setoran/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Buat Setoran')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('setoran.index') }}">Setoran</a></li>
    <li class="breadcrumb-item active">Buat Setoran</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">Buat Setoran</h4>
        <p class="text-muted small mb-0">Rekap pembayaran untuk disetor</p>
    </div>
    <a href="{{ route('setoran.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

{{-- ✅ FIX: Tampilkan pilihan jenjang untuk admin yayasan --}}
@if($pilihJenjang)
<div class="card mb-4">
    <div class="card-header py-3" style="background:var(--primary);color:white">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-funnel me-2"></i>Pilih Jenjang Setoran
        </h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Pilih jenjang untuk menampilkan daftar pembayaran yang belum disetor.
        </p>
        <div class="row g-3">
            @foreach(['TK' => ['icon'=>'bi-flower1','color'=>'#db2777','bg'=>'#fce7f3','border'=>'#f9a8d4'],
                      'SD' => ['icon'=>'bi-book','color'=>'#1d4ed8','bg'=>'#dbeafe','border'=>'#93c5fd'],
                      'SMP'=> ['icon'=>'bi-mortarboard','color'=>'#059669','bg'=>'#d1fae5','border'=>'#6ee7b7']] as $j => $style)
            <div class="col-md-4">
                <a href="{{ route('setoran.create', ['jenjang' => $j]) }}"
                   class="text-decoration-none">
                    <div class="rounded-3 p-4 text-center h-100 {{ $jenjang === $j ? 'shadow' : '' }}"
                         style="background:{{ $style['bg'] }};border:2px solid {{ $jenjang === $j ? $style['color'] : $style['border'] }};
                                transition:.2s;cursor:pointer">
                        <i class="bi {{ $style['icon'] }} fs-2 mb-2 d-block" style="color:{{ $style['color'] }}"></i>
                        <div class="fw-bold" style="color:{{ $style['color'] }}">{{ $j }}</div>
                        @if($jenjang === $j)
                            <span class="badge mt-1" style="background:{{ $style['color'] }};color:white">
                                <i class="bi bi-check me-1"></i>Dipilih
                            </span>
                        @endif
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Tampilkan form setoran hanya jika jenjang sudah dipilih --}}
@if($jenjang)

@php
    $jStyle = match($jenjang) {
        'TK'  => ['color'=>'#db2777','bg'=>'#fce7f3','border'=>'#f9a8d4'],
        'SD'  => ['color'=>'#1d4ed8','bg'=>'#dbeafe','border'=>'#93c5fd'],
        'SMP' => ['color'=>'#059669','bg'=>'#d1fae5','border'=>'#6ee7b7'],
        default => ['color'=>'#64748b','bg'=>'#f1f5f9','border'=>'#e2e8f0'],
    };
@endphp

<form method="POST" action="{{ route('setoran.store') }}" id="formSetoran">
@csrf
<input type="hidden" name="jenjang" value="{{ $jenjang }}">

<div class="row g-3">

    {{-- ═══ KIRI: Tabel Pembayaran ═══════════════════════════════ --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-list-check me-2" style="color:var(--primary)"></i>
                    Pembayaran Belum Disetor
                    <span class="badge ms-2 rounded-pill" style="background:{{ $jStyle['bg'] }};color:{{ $jStyle['color'] }};border:1px solid {{ $jStyle['border'] }}">
                        {{ $jenjang }}
                    </span>
                </h6>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnCeklisSemua">
                        <i class="bi bi-check-all me-1"></i>Pilih Semua
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnHapusSemua">
                        <i class="bi bi-x me-1"></i>Hapus Semua
                    </button>
                </div>
            </div>
            <div class="card-body p-0">

                @if($pembayaranBelumSetor->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-check-circle fs-2 d-block text-success mb-2"></i>
                    <div class="fw-600 mb-1">Semua pembayaran {{ $jenjang }} sudah disetor</div>
                    <div class="small">Tidak ada pembayaran yang perlu disetorkan.</div>
                </div>
                @else

                {{-- Filter & Info --}}
                <div class="px-3 py-2 border-bottom" style="background:#f8fafc">
                    <div class="d-flex align-items-center gap-3 flex-wrap small text-muted">
                        <span>
                            <i class="bi bi-receipt me-1"></i>
                            <strong class="text-dark">{{ $pembayaranBelumSetor->count() }}</strong> pembayaran menunggu setoran
                        </span>
                        <span>
                            <i class="bi bi-cash me-1"></i>
                            Total: <strong class="text-success">
                                Rp {{ number_format($pembayaranBelumSetor->sum('total_bayar'), 0, ',', '.') }}
                            </strong>
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th style="width:40px">
                                    <input type="checkbox" id="checkAll" class="form-check-input"
                                           title="Pilih semua">
                                </th>
                                <th>Tanggal</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Bulan</th>
                                <th class="text-end">SPP</th>
                                @if($jenjang === 'TK')
                                <th class="text-end">Mamin</th>
                                @endif
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pembayaranBelumSetor as $p)
                            <tr class="tr-bayar"
                                data-total="{{ $p->total_bayar }}"
                                data-spp="{{ $p->nominal_per_bulan * $p->jumlah_bulan + $p->nominal_donator }}"
                                data-mamin="{{ $p->nominal_mamin }}">
                                <td>
                                    <input type="checkbox"
                                           name="pembayaran_ids[]"
                                           value="{{ $p->id }}"
                                           class="form-check-input chk-bayar">
                                </td>
                                <td class="small text-muted">
                                    {{ $p->tanggal_bayar->format('d/m/Y') }}
                                </td>
                                <td>
                                    <div class="fw-600 small">{{ $p->siswa->nama ?? '-' }}</div>
                                    <div class="text-muted" style="font-size:.7rem">
                                        {{ $p->kode_bayar }}
                                    </div>
                                </td>
                                <td class="small">{{ $p->siswa->kelas ?? '-' }}</td>
                                <td class="small">
                                    <span title="{{ $p->bulan_label }}">
                                        @php $bulanArr = $p->bulan_bayar ?? []; @endphp
                                        @if(count($bulanArr) > 2)
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $bulanArr[0])->isoFormat('MMM YY') }}
                                            <span class="badge bg-secondary-subtle text-secondary">+{{ count($bulanArr)-1 }}</span>
                                        @else
                                            {{ $p->bulan_label }}
                                        @endif
                                    </span>
                                    <div class="text-muted" style="font-size:.7rem">{{ $p->jumlah_bulan }} bulan</div>
                                </td>
                                <td class="text-end small">
                                    Rp {{ number_format($p->nominal_per_bulan * $p->jumlah_bulan + $p->nominal_donator, 0, ',', '.') }}
                                </td>
                                @if($jenjang === 'TK')
                                <td class="text-end small" style="color:#6366f1">
                                    @if($p->nominal_mamin > 0)
                                        Rp {{ number_format($p->nominal_mamin, 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                @endif
                                <td class="text-end fw-bold text-success small">
                                    Rp {{ number_format($p->total_bayar, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @endif {{-- end isEmpty --}}

            </div>
        </div>
    </div>

    {{-- ═══ KANAN: Rekap & Simpan ══════════════════════════════════ --}}
    <div class="col-md-4">
        <div class="card sticky-top" style="top:80px">
            <div class="card-header py-3" style="background: var(--primary); color:white">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-calculator me-2"></i>Rekap Setoran
                </h6>
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label fw-600 small">
                        Tanggal Setoran <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="tanggal_setoran" class="form-control"
                           value="{{ date('Y-m-d') }}" required>
                </div>

                {{-- Ringkasan --}}
                <div class="rounded-3 p-3 mb-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Dipilih</span>
                        <strong><span id="jmlDipilih">0</span> pembayaran</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Total SPP</span>
                        <strong id="totalSPP">Rp 0</strong>
                    </div>
                    @if($jenjang === 'TK')
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Total Mamin</span>
                        <strong style="color:#6366f1"><span id="totalMamin">Rp 0</span></strong>
                    </div>
                    @endif
                    <hr class="my-2">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold small">TOTAL</span>
                        <strong class="text-primary" style="font-size:1.1rem">
                            <span id="totalSemua">Rp 0</span>
                        </strong>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-600">Keterangan (Opsional)</label>
                    <textarea name="keterangan" class="form-control form-control-sm" rows="2"
                              placeholder="Catatan setoran..."></textarea>
                </div>

                @if(!$pembayaranBelumSetor->isEmpty())
                <button type="submit" class="btn btn-primary w-100" id="btnSimpanSetoran" disabled>
                    <i class="bi bi-save me-2"></i>Simpan Setoran
                </button>
                <div class="text-center mt-2 small text-muted" id="infoHelper">
                    Pilih minimal 1 pembayaran
                </div>
                @else
                <a href="{{ route('setoran.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
                </a>
                @endif

            </div>
        </div>
    </div>

</div>
</form>

@endif {{-- end jenjang --}}

@endsection

@push('scripts')
<script>
function hitungTotal() {
    const checked = document.querySelectorAll('.chk-bayar:checked');
    let totalSPP = 0, totalMamin = 0, totalSemua = 0;

    checked.forEach(chk => {
        const row = chk.closest('tr');
        totalSPP   += parseFloat(row.dataset.spp   || 0);
        totalMamin += parseFloat(row.dataset.mamin || 0);
        totalSemua += parseFloat(row.dataset.total || 0);
    });

    document.getElementById('jmlDipilih').textContent = checked.length;
    document.getElementById('totalSPP').textContent   = 'Rp ' + fmt(totalSPP);

    const elMamin = document.getElementById('totalMamin');
    if (elMamin) elMamin.textContent = 'Rp ' + fmt(totalMamin);

    document.getElementById('totalSemua').textContent = 'Rp ' + fmt(totalSemua);

    const btnSimpan  = document.getElementById('btnSimpanSetoran');
    const infoHelper = document.getElementById('infoHelper');

    if (btnSimpan) {
        btnSimpan.disabled = checked.length === 0;
        if (infoHelper) {
            infoHelper.textContent = checked.length === 0
                ? 'Pilih minimal 1 pembayaran'
                : `${checked.length} pembayaran dipilih — Total: Rp ${fmt(totalSemua)}`;
            infoHelper.className = checked.length === 0
                ? 'text-center mt-2 small text-muted'
                : 'text-center mt-2 small text-success fw-600';
        }
    }

    // Sinkron state checkAll
    const semua = document.querySelectorAll('.chk-bayar');
    const checkAll = document.getElementById('checkAll');
    if (checkAll && semua.length > 0) {
        checkAll.checked       = checked.length === semua.length;
        checkAll.indeterminate = checked.length > 0 && checked.length < semua.length;
    }
}

// Event listener semua checkbox baris
document.querySelectorAll('.chk-bayar').forEach(c => c.addEventListener('change', hitungTotal));

// Checkbox "Pilih Semua" di header
const checkAll = document.getElementById('checkAll');
if (checkAll) {
    checkAll.addEventListener('change', function () {
        document.querySelectorAll('.chk-bayar').forEach(c => c.checked = this.checked);
        hitungTotal();
    });
}

// Tombol Pilih Semua
document.getElementById('btnCeklisSemua')?.addEventListener('click', function () {
    document.querySelectorAll('.chk-bayar').forEach(c => c.checked = true);
    if (checkAll) checkAll.checked = true;
    hitungTotal();
});

// Tombol Hapus Semua
document.getElementById('btnHapusSemua')?.addEventListener('click', function () {
    document.querySelectorAll('.chk-bayar').forEach(c => c.checked = false);
    if (checkAll) { checkAll.checked = false; checkAll.indeterminate = false; }
    hitungTotal();
});

// Highlight baris yang dipilih
document.querySelectorAll('.chk-bayar').forEach(c => {
    c.addEventListener('change', function () {
        this.closest('tr').style.background = this.checked ? '#f0f9ff' : '';
    });
});

function fmt(n) {
    return new Intl.NumberFormat('id-ID').format(Math.round(n));
}
</script>
@endpush