{{-- resources/views/cetak/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Cetak Kartu')

@section('breadcrumb')
    <li class="breadcrumb-item active">Cetak Kartu</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="font-family:'Sora',sans-serif;color:var(--ink)">Cetak Kartu Pembayaran</h4>
        <p class="mb-0" style="color:var(--ink-muted);font-size:.85rem">Format F4 — 4 kartu per halaman</p>
    </div>
</div>

<form method="POST" action="{{ route('cetak.kartu') }}" target="_blank">
@csrf

<div class="row g-3">

    {{-- ── Tabel Siswa ── --}}
    <div class="col-md-9">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                 style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0 fw-bold" style="color:var(--ink)">
                    <i class="bi bi-person-lines-fill me-2" style="color:var(--blue)"></i>Pilih Siswa
                </h6>
                <div class="d-flex gap-2 align-items-center">
                    <input type="text" id="searchFilter" class="form-control form-control-sm"
                           placeholder="Cari nama..." style="width:160px">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnCeklisSemua">
                        <i class="bi bi-check-all me-1"></i>Pilih Semua
                    </button>
                </div>
            </div>
            <div class="card-body p-0" style="max-height:500px;overflow-y:auto">
                <table class="table mb-0">
                    <thead class="sticky-top bg-white">
                        <tr>
                            <th style="width:40px">
                                <input type="checkbox" class="form-check-input" id="checkAll">
                            </th>
                            <th>Nama Siswa</th>
                            <th>Jenjang</th>
                            <th>Kelas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="tableSiswa">
                        @foreach($siswa as $s)
                        <tr>
                            <td>
                                <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}"
                                       class="form-check-input chk-siswa">
                            </td>
                            <td class="fw-600" style="color:var(--ink)">{{ $s->nama }}</td>
                            <td><span class="badge-{{ $s->jenjang }}">{{ $s->jenjang }}</span></td>
                            <td style="color:var(--ink-soft);font-size:.875rem">{{ $s->kelas }}</td>
                            <td>
                                @if($s->status === 'aktif')
                                    <span style="display:inline-flex;align-items:center;gap:.3rem;
                                                 font-size:.68rem;font-weight:600;padding:.25rem .65rem;
                                                 border-radius:999px;background:var(--green-pale);
                                                 color:#065F46;border:1px solid #6EE7B7">
                                        <span style="width:5px;height:5px;border-radius:50%;background:var(--green);flex-shrink:0"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span style="display:inline-flex;align-items:center;
                                                 font-size:.68rem;font-weight:600;padding:.25rem .65rem;
                                                 border-radius:999px;background:var(--bg);
                                                 color:var(--ink-muted);border:1px solid var(--border)">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Panel Cetak ── --}}
    <div class="col-md-3">
        <div class="card sticky-top" style="top:80px">
            <div class="card-header py-3" style="background:var(--navy)">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-printer me-2"></i>Opsi Cetak
                </h6>
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                    @php
                        $bulanSekarang = (int) date('m');
                        $tahunDefault  = $bulanSekarang >= 7 ? (int) date('Y') : (int) date('Y') - 1;
                    @endphp
                    <select name="tahun_ajaran" class="form-select">
                        @for($y = $tahunDefault; $y >= $tahunDefault - 2; $y--)
                        <option value="{{ $y }}" {{ $y == $tahunDefault ? 'selected' : '' }}>
                            {{ $y }}/{{ $y + 1 }}
                        </option>
                        @endfor
                    </select>
                </div>

                {{-- Info box --}}
                <div class="rounded-3 p-3 mb-3" style="background:var(--blue-pale);border:1px solid var(--blue-light)">
                    <div class="d-flex align-items-start gap-2" style="font-size:.83rem;color:var(--blue-dark)">
                        <i class="bi bi-info-circle mt-1 flex-shrink-0"></i>
                        <div>
                            <span id="infoTerpilih" class="fw-bold">0</span> siswa dipilih.<br>
                            Akan tercetak <span id="infoHalaman" class="fw-bold">0</span> halaman.
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100" id="btnCetak" disabled>
                    <i class="bi bi-printer me-2"></i>Cetak Kartu PDF
                </button>

            </div>
        </div>
    </div>

</div>
</form>

@endsection

@push('scripts')
<script>
function updateCount() {
    const checked = document.querySelectorAll('.chk-siswa:checked').length;
    document.getElementById('infoTerpilih').textContent = checked;
    document.getElementById('infoHalaman').textContent  = Math.ceil(checked / 4);
    document.getElementById('btnCetak').disabled = checked === 0;

    // Sinkron checkAll state
    const semua = document.querySelectorAll('.chk-siswa');
    const checkAll = document.getElementById('checkAll');
    checkAll.checked       = checked === semua.length && semua.length > 0;
    checkAll.indeterminate = checked > 0 && checked < semua.length;
}

document.querySelectorAll('.chk-siswa').forEach(c => {
    c.addEventListener('change', function () {
        this.closest('tr').style.background = this.checked ? 'var(--blue-pale)' : '';
        updateCount();
    });
});

document.getElementById('checkAll').addEventListener('change', function () {
    document.querySelectorAll('.chk-siswa').forEach(c => {
        c.checked = this.checked;
        c.closest('tr').style.background = this.checked ? 'var(--blue-pale)' : '';
    });
    updateCount();
});

document.getElementById('btnCeklisSemua').addEventListener('click', function () {
    document.querySelectorAll('.chk-siswa').forEach(c => {
        c.checked = true;
        c.closest('tr').style.background = 'var(--blue-pale)';
    });
    document.getElementById('checkAll').checked = true;
    updateCount();
});

document.getElementById('searchFilter').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#tableSiswa tr').forEach(tr => {
        const nama = tr.querySelector('td:nth-child(2)')?.textContent.toLowerCase() ?? '';
        tr.style.display = nama.includes(q) ? '' : 'none';
    });
});
</script>
@endpush