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

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">Edit Pembayaran</h4>
        <p class="text-muted small mb-0"><code>{{ $pembayaran->kode_bayar }}</code></p>
    </div>
    <a href="{{ route('pembayaran.show', $pembayaran) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row justify-content-center">
<div class="col-lg-7">

    {{-- Info pembayaran (readonly) --}}
    <div class="card mb-3">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold" style="color:var(--primary)">
                <i class="bi bi-info-circle me-2"></i>Informasi Pembayaran
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small">Siswa</div>
                    <div class="fw-bold">{{ $pembayaran->siswa->nama ?? '-' }}</div>
                    <div class="text-muted small">
                        {{ $pembayaran->siswa->jenjang ?? '' }} Kelas {{ $pembayaran->siswa->kelas ?? '' }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Bulan Dibayar</div>
                    <div class="fw-600">{{ $pembayaran->bulan_label }}</div>
                    <div class="text-muted small">{{ $pembayaran->jumlah_bulan }} bulan</div>
                </div>
            </div>

            {{-- Rincian biaya dengan rumus --}}
            <div class="rounded-3 p-3 mt-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                <div class="d-flex flex-wrap gap-3 small align-items-center">
                    <div>
                        <span class="text-muted">SPP</span>
                        <div class="fw-bold text-primary">
                            Rp {{ number_format($pembayaran->nominal_per_bulan, 0, ',', '.') }}/bln
                            × {{ $pembayaran->jumlah_bulan }}
                        </div>
                    </div>
                    <span class="text-danger fw-bold">−</span>
                    <div>
                        <span class="text-muted">Donatur</span>
                        <div class="fw-bold text-danger" id="previewDonatur">
                            Rp {{ number_format($pembayaran->nominal_donator / max($pembayaran->jumlah_bulan,1), 0, ',', '.') }}/bln
                        </div>
                    </div>
                    @if($pembayaran->nominal_mamin > 0)
                    <span class="text-success fw-bold">+</span>
                    <div>
                        <span class="text-muted">Mamin</span>
                        <div class="fw-bold text-info">
                            Rp {{ number_format($pembayaran->nominal_mamin / max($pembayaran->jumlah_bulan,1), 0, ',', '.') }}/bln
                        </div>
                    </div>
                    @endif
                    <span class="text-muted fw-bold">=</span>
                    <div>
                        <span class="text-muted">Total</span>
                        <div class="fw-bold text-success fs-6" id="previewTotal">
                            Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
                <div class="mt-2" style="font-size:.72rem;color:#64748b">
                    <i class="bi bi-info-circle me-1"></i>
                    Rumus: (SPP − Donatur + Mamin) × {{ $pembayaran->jumlah_bulan }} bulan
                </div>
            </div>
        </div>
    </div>

    {{-- Form edit --}}
    <div class="card mb-4">
        <div class="card-header py-3" style="background:var(--primary);color:white">
            <h6 class="mb-0 fw-bold"><i class="bi bi-pencil me-2"></i>Edit Data</h6>
        </div>
        <div class="card-body">

            <form method="POST" action="{{ route('pembayaran.update', $pembayaran) }}">
            @csrf
            @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-600">Tanggal Bayar <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_bayar"
                           value="{{ old('tanggal_bayar', $pembayaran->tanggal_bayar->format('Y-m-d')) }}"
                           class="form-control @error('tanggal_bayar') is-invalid @enderror" required>
                    @error('tanggal_bayar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">
                        Donatur / Bulan
                        <small class="text-muted">(akan dihitung ulang)</small>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="nominal_donator" id="inputDonatur"
                               value="{{ old('nominal_donator', (int) ($pembayaran->nominal_donator / max($pembayaran->jumlah_bulan, 1))) }}"
                               class="form-control @error('nominal_donator') is-invalid @enderror"
                               min="0" step="1000">
                        <span class="input-group-text text-muted small">/bln</span>
                    </div>
                    @error('nominal_donator')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Preview total baru --}}
                <div class="rounded-3 p-3 mb-4" style="background:#f0fdf4;border:1px solid #bbf7d0">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">
                            SPP ({{ $pembayaran->jumlah_bulan }} bln):
                        </span>
                        <strong>Rp {{ number_format($pembayaran->nominal_per_bulan * $pembayaran->jumlah_bulan, 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-1 text-danger">
                        <span>Donatur (<span id="lblDonaturBln">{{ (int)($pembayaran->nominal_donator / max($pembayaran->jumlah_bulan,1)) }}</span>/bln × {{ $pembayaran->jumlah_bulan }}):</span>
                        <strong id="previewDonaturTotal">− Rp {{ number_format($pembayaran->nominal_donator, 0, ',', '.') }}</strong>
                    </div>
                    @if($pembayaran->nominal_mamin > 0)
                    <div class="d-flex justify-content-between small mb-1 text-info">
                        <span>Mamin ({{ $pembayaran->jumlah_bulan }} bln):</span>
                        <strong>+ Rp {{ number_format($pembayaran->nominal_mamin, 0, ',', '.') }}</strong>
                    </div>
                    @endif
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold small">Total Baru:</span>
                        <strong class="text-success" style="font-size:1.05rem" id="previewTotalLive">
                            Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}
                        </strong>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-600">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2"
                              placeholder="Catatan (opsional)">{{ old('keterangan', $pembayaran->keterangan) }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                    </button>
                    <a href="{{ route('pembayaran.show', $pembayaran) }}"
                       class="btn btn-outline-secondary">Batal</a>
                </div>

            </form>
        </div>
    </div>

</div>
</div>

@endsection

@push('scripts')
<script>
// Nilai dari server (sudah per bulan atau total sesuai konteks)
const sppPerBulan   = {{ $pembayaran->nominal_per_bulan }};
const jumlahBulan   = {{ $pembayaran->jumlah_bulan }};
// nominal_mamin di DB tersimpan sebagai total (mamin × bulan)
const maminPerBulan = {{ $pembayaran->jumlah_bulan > 0 ? $pembayaran->nominal_mamin / $pembayaran->jumlah_bulan : 0 }};

document.getElementById('inputDonatur').addEventListener('input', function () {
    const donaturPerBulan = parseFloat(this.value) || 0;
    const donaturTotal    = donaturPerBulan * jumlahBulan;
    const maminTotal      = maminPerBulan * jumlahBulan;
    const sppTotal        = sppPerBulan * jumlahBulan;

    // ✅ RUMUS BENAR: (SPP - Donatur + Mamin) × jumlah_bulan
    const total = (sppPerBulan - donaturPerBulan + maminPerBulan) * jumlahBulan;

    const fmt = n => new Intl.NumberFormat('id-ID').format(Math.round(n));

    document.getElementById('lblDonaturBln').textContent       = fmt(donaturPerBulan);
    document.getElementById('previewDonaturTotal').textContent = '− Rp ' + fmt(donaturTotal);
    document.getElementById('previewTotalLive').textContent    = 'Rp ' + fmt(total);
});
</script>
@endpush