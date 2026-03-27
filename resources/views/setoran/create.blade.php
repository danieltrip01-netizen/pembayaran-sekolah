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
        <h4 class="fw-bold mb-1" style="font-family:'Sora',sans-serif;color:var(--ink)">Buat Setoran</h4>
        <p class="mb-0 d-flex align-items-center gap-2 flex-wrap" style="color:var(--ink-muted);font-size:.85rem">
            <span>Rekap pembayaran untuk disetor</span>
            @if($tahunPelajaran)
                <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:600;
                             padding:.18rem .6rem;border-radius:999px;
                             background:#d1fae5;color:#065F46;border:1px solid #6ee7b7">
                    <i class="bi bi-calendar-check"></i>T.A. {{ $tahunPelajaran->nama }}
                </span>
            @else
                <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:600;
                             padding:.18rem .6rem;border-radius:999px;
                             background:var(--red-pale);color:var(--red);border:1px solid #fecaca">
                    <i class="bi bi-exclamation-circle"></i>Tidak ada T.A. aktif
                </span>
            @endif
        </p>
    </div>
    <a href="{{ route('setoran.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@if(!$tahunPelajaran)
<div class="rounded-3 p-3 mb-4 d-flex align-items-center gap-3"
     style="background:#fff7ed;border:1px solid #fed7aa">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"
       style="color:var(--orange);font-size:1.2rem"></i>
    <div style="font-size:.85rem">
        <div class="fw-bold" style="color:#92400e">Tidak ada tahun pelajaran aktif</div>
        <div style="color:var(--ink-muted)">
            Setoran tidak dapat dibuat. Harap aktifkan tahun pelajaran terlebih dahulu.
            <a href="{{ route('tahun-pelajaran.index') }}" class="fw-bold ms-1">
                Aktifkan tahun pelajaran &#8594;
            </a>
        </div>
    </div>
</div>
@endif

{{-- Pilihan Jenjang untuk admin yayasan --}}
@if($pilihJenjang)
<div class="card mb-4">
    <div class="card-header py-3" style="background:var(--navy)">
        <h6 class="mb-0 fw-bold text-white">
            <i class="bi bi-funnel me-2"></i>Pilih Jenjang Setoran
        </h6>
    </div>
    <div class="card-body">
        <p class="mb-3" style="font-size:.85rem;color:var(--ink-muted)">
            Pilih jenjang untuk menampilkan daftar pembayaran yang belum disetor.
        </p>
        <div class="row g-3">
            @foreach([
                'TK'  => ['icon'=>'bi-flower1',    'color'=>'#B45309','bg'=>'var(--yellow-pale)','border'=>'#FDE68A','active_border'=>'#F59E0B'],
                'SD'  => ['icon'=>'bi-book',        'color'=>'var(--blue-dark)','bg'=>'var(--blue-pale)','border'=>'var(--blue-light)','active_border'=>'var(--blue)'],
                'SMP' => ['icon'=>'bi-mortarboard', 'color'=>'#065F46','bg'=>'var(--green-pale)','border'=>'#6EE7B7','active_border'=>'var(--green)'],
            ] as $j => $style)
            <div class="col-md-4">
                <a href="{{ route('setoran.create', ['jenjang' => $j]) }}" class="text-decoration-none">
                    <div class="rounded-3 p-4 text-center h-100"
                         style="background:{{ $style['bg'] }};
                                border:2px solid {{ $jenjang === $j ? $style['active_border'] : $style['border'] }};
                                transition:.2s;cursor:pointer;
                                {{ $jenjang === $j ? 'box-shadow:0 4px 16px rgba(0,0,0,.08)' : '' }}">
                        <i class="bi {{ $style['icon'] }} d-block mb-2"
                           style="font-size:1.75rem;color:{{ $style['color'] }}"></i>
                        <div class="fw-bold" style="color:{{ $style['color'] }};font-family:'Sora',sans-serif">
                            {{ $j }}
                        </div>
                        @if($jenjang === $j)
                            <span class="mt-2 d-inline-flex align-items-center gap-1"
                                  style="font-size:.72rem;font-weight:600;padding:.2rem .6rem;
                                         border-radius:999px;background:{{ $style['active_border'] }};color:white">
                                <i class="bi bi-check2"></i>Dipilih
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

@if($jenjang)

<form method="POST" action="{{ route('setoran.store') }}" id="formSetoran">
@csrf
<input type="hidden" name="jenjang" value="{{ $jenjang }}">

<div class="row g-3">

    {{-- ═══ KIRI: Tabel Pembayaran ═══════════════════════════════ --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                 style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0 fw-bold" style="color:var(--ink)">
                    <i class="bi bi-list-check me-2" style="color:var(--blue)"></i>
                    Pembayaran Belum Disetor
                </h6>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnCeklisSemua">
                        <i class="bi bi-check-all me-1"></i>Pilih Semua
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnHapusSemua">
                        <i class="bi bi-x me-1"></i>Hapus Semua
                    </button>
                </div>
            </div>

            <div class="card-body p-0">

                @if($pembayaranBelumSetor->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-check-circle d-block mb-2" style="font-size:2rem;color:var(--green)"></i>
                    <div class="fw-600 mb-1" style="color:var(--ink-soft)">
                        Semua pembayaran {{ $jenjang }} sudah disetor
                    </div>
                    <div style="font-size:.85rem;color:var(--ink-muted)">
                        Tidak ada pembayaran yang perlu disetorkan.
                    </div>
                </div>
                @else

                {{-- Info bar --}}
                <div class="px-3 py-2" style="background:var(--bg);border-bottom:1px solid var(--border)">
                    <div class="d-flex align-items-center gap-3 flex-wrap" style="font-size:.82rem;color:var(--ink-muted)">
                        <span>
                            <i class="bi bi-receipt me-1"></i>
                            <strong style="color:var(--ink)">{{ $pembayaranBelumSetor->count() }}</strong>
                            pembayaran menunggu setoran
                        </span>
                        <span>
                            <i class="bi bi-cash me-1"></i>
                            Total: <strong style="color:var(--green)">
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
                                    <input type="checkbox" id="checkAll" class="form-check-input" title="Pilih semua">
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
                                data-spp="{{ $p->total_bayar - $p->nominal_mamin }}"
                                data-mamin="{{ $p->nominal_mamin }}">
                                <td>
                                    <input type="checkbox"
                                           name="pembayaran_ids[]"
                                           value="{{ $p->id }}"
                                           class="form-check-input chk-bayar">
                                </td>
                                <td style="font-size:.82rem;color:var(--ink-muted)">
                                    {{ $p->tanggal_bayar->format('d/m/Y') }}
                                </td>
                                <td>
                                    <div class="fw-600" style="font-size:.85rem;color:var(--ink)">
                                        {{ $p->siswa->nama ?? '—' }}
                                    </div>
                                    <div style="font-size:.72rem;color:var(--ink-faint)">
                                        {{ $p->kode_bayar }}
                                    </div>
                                </td>
                                <td style="font-size:.85rem;color:var(--ink-soft)">
                                    {{ $p->siswaKelas?->kelas?->nama ?? '—' }}
                                </td>
                                <td style="font-size:.85rem;color:var(--ink-soft)">
                                    {{-- Kolom bulan_bayar (JSON) sudah dihapus — gunakan relasi pembayaranBulan --}}
                                    @php $bulanArr = $p->pembayaranBulan; @endphp
                                    <span title="{{ $p->bulan_label }}">
                                        @if($bulanArr->count() > 2)
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $bulanArr->first()->bulan)->isoFormat('MMM YY') }}
                                            <span style="display:inline-flex;align-items:center;font-size:.68rem;
                                                         font-weight:600;padding:.15rem .45rem;border-radius:999px;
                                                         background:var(--bg);color:var(--ink-muted);border:1px solid var(--border)">
                                                +{{ $bulanArr->count() - 1 }}
                                            </span>
                                        @else
                                            {{ $p->bulan_label }}
                                        @endif
                                    </span>
                                </td>
                                <td class="text-end" style="font-size:.85rem;color:var(--ink-soft)">
                                    Rp {{ number_format($p->total_bayar - $p->nominal_mamin, 0, ',', '.') }}
                                </td>
                                @if($jenjang === 'TK')
                                <td class="text-end" style="font-size:.85rem;color:#6366f1">
                                    @if($p->nominal_mamin > 0)
                                        Rp {{ number_format($p->nominal_mamin, 0, ',', '.') }}
                                    @else
                                        <span style="color:var(--ink-faint)">—</span>
                                    @endif
                                </td>
                                @endif
                                <td class="text-end fw-bold" style="font-size:.85rem;color:var(--green)">
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
            <div class="card-header py-3" style="background:var(--navy)">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-calculator me-2"></i>Rekap Setoran
                </h6>
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">
                        Tanggal Setoran <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="tanggal_setoran" class="form-control"
                           value="{{ date('Y-m-d') }}" required>
                </div>

                {{-- Ringkasan --}}
                <div class="rounded-3 p-3 mb-3" style="background:var(--bg);border:1px solid var(--border)">
                    <div class="d-flex justify-content-between mb-2" style="font-size:.85rem">
                        <span style="color:var(--ink-muted)">Dipilih</span>
                        <strong style="color:var(--ink)">
                            <span id="jmlDipilih">0</span> pembayaran
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2" style="font-size:.85rem">
                        <span style="color:var(--ink-muted)">Total SPP</span>
                        <strong id="totalSPP" style="color:var(--ink)">Rp 0</strong>
                    </div>
                    @if($jenjang === 'TK')
                    <div class="d-flex justify-content-between mb-2" style="font-size:.85rem">
                        <span style="color:var(--ink-muted)">Total Mamin</span>
                        <strong style="color:#6366f1"><span id="totalMamin">Rp 0</span></strong>
                    </div>
                    @endif
                    <div style="height:1px;background:var(--border);margin:.6rem 0"></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold" style="font-size:.85rem;color:var(--ink-soft)">TOTAL</span>
                        <strong style="font-size:1.1rem;color:var(--navy)">
                            <span id="totalSemua">Rp 0</span>
                        </strong>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan <span style="color:var(--ink-faint);font-weight:400">(Opsional)</span></label>
                    <textarea name="keterangan" class="form-control form-control-sm" rows="2"
                              placeholder="Catatan setoran..."></textarea>
                </div>

                @if(!$pembayaranBelumSetor->isEmpty())
                <button type="submit" class="btn btn-primary w-100" id="btnSimpanSetoran" disabled>
                    <i class="bi bi-save me-2"></i>Simpan Setoran
                </button>
                <div class="text-center mt-2" id="infoHelper"
                     style="font-size:.8rem;color:var(--ink-muted)">
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
        totalSPP   += parseFloat(row.dataset.total   || 0);
        totalMamin += parseFloat(row.dataset.mamin || 0);
        totalSemua += parseFloat(row.dataset.spp || 0);
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
            infoHelper.style.color = checked.length === 0
                ? 'var(--ink-muted)' : 'var(--green)';
            infoHelper.style.fontWeight = checked.length === 0 ? '400' : '600';
        }
    }

    const semua    = document.querySelectorAll('.chk-bayar');
    const checkAll = document.getElementById('checkAll');
    if (checkAll && semua.length > 0) {
        checkAll.checked       = checked.length === semua.length;
        checkAll.indeterminate = checked.length > 0 && checked.length < semua.length;
    }
}

document.querySelectorAll('.chk-bayar').forEach(c => c.addEventListener('change', hitungTotal));

const checkAll = document.getElementById('checkAll');
if (checkAll) {
    checkAll.addEventListener('change', function () {
        document.querySelectorAll('.chk-bayar').forEach(c => c.checked = this.checked);
        hitungTotal();
    });
}

document.getElementById('btnCeklisSemua')?.addEventListener('click', function () {
    document.querySelectorAll('.chk-bayar').forEach(c => c.checked = true);
    if (checkAll) checkAll.checked = true;
    hitungTotal();
});

document.getElementById('btnHapusSemua')?.addEventListener('click', function () {
    document.querySelectorAll('.chk-bayar').forEach(c => c.checked = false);
    if (checkAll) { checkAll.checked = false; checkAll.indeterminate = false; }
    hitungTotal();
});

// Highlight baris dipilih
document.querySelectorAll('.chk-bayar').forEach(c => {
    c.addEventListener('change', function () {
        this.closest('tr').style.background = this.checked ? 'var(--blue-pale)' : '';
    });
});

function fmt(n) {
    return new Intl.NumberFormat('id-ID').format(Math.round(n));
}
</script>
@endpush