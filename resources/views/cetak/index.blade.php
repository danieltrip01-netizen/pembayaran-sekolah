{{-- resources/views/cetak/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Cetak Kartu')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">Cetak Kartu Pembayaran</h4>
        <p class="text-muted small mb-0">Format F4 — 4 kartu per halaman</p>
    </div>
</div>

<form method="POST" action="{{ route('cetak.kartu') }}" target="_blank">
@csrf

<div class="row g-3">
    <div class="col-md-9">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill me-2"></i>Pilih Siswa</h6>
                <div class="d-flex gap-2 align-items-center">
                    <input type="text" id="searchFilter" class="form-control form-control-sm" placeholder="Cari nama..." style="width:160px">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnCeklisSemua">Pilih Semua</button>
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
                            <td><input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="form-check-input chk-siswa"></td>
                            <td class="fw-600">{{ $s->nama }}</td>
                            <td><span class="badge badge-{{ strtolower($s->jenjang) }}">{{ $s->jenjang }}</span></td>
                            <td>{{ $s->kelas }}</td>
                            <td>
                                @if($s->status === 'aktif')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sticky-top" style="top:80px">
            <div class="card-header py-3" style="background: var(--primary); color:white">
                <h6 class="mb-0 fw-bold"><i class="bi bi-printer me-2"></i>Opsi Cetak</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-600">Tahun Ajaran <span class="text-danger">*</span></label>
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

                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-1"></i>
                    <span id="infoTerpilih">0</span> siswa dipilih.
                    Akan tercetak <span id="infoHalaman">0</span> halaman.
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
}

document.querySelectorAll('.chk-siswa').forEach(c => c.addEventListener('change', updateCount));
document.getElementById('checkAll').addEventListener('change', function() {
    document.querySelectorAll('.chk-siswa').forEach(c => c.checked = this.checked);
    updateCount();
});
document.getElementById('btnCeklisSemua').addEventListener('click', function() {
    document.querySelectorAll('.chk-siswa').forEach(c => c.checked = true);
    document.getElementById('checkAll').checked = true;
    updateCount();
});

document.getElementById('searchFilter').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#tableSiswa tr').forEach(tr => {
        const nama = tr.querySelector('td:nth-child(2)')?.textContent.toLowerCase() ?? '';
        tr.style.display = nama.includes(q) ? '' : 'none';
    });
});
</script>
@endpush