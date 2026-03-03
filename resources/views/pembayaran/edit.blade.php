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
        <h4 class="fw-bold mb-1" style="color: var(--navy); font-family:'Sora',sans-serif;">
            Edit Pembayaran
        </h4>
        <p class="mb-0" style="color:var(--ink-muted);font-size:.85rem;">
            <code style="color:var(--navy);font-size:.8rem;">{{ $pembayaran->kode_bayar }}</code>
        </p>
    </div>
    <a href="{{ route('pembayaran.show', $pembayaran) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row justify-content-center">
<div class="col-lg-7">

    {{-- Info pembayaran (readonly) --}}
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                <i class="bi bi-info-circle me-2"></i>Informasi Pembayaran
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div style="color:var(--ink-muted);font-size:.78rem;font-weight:600;
                                text-transform:uppercase;letter-spacing:.5px;">Siswa</div>
                    <div class="fw-600 mt-1" style="color:var(--ink);">
                        {{ $pembayaran->siswa->nama ?? '—' }}
                    </div>
                    <div style="color:var(--ink-muted);font-size:.8rem;">
                        {{ $pembayaran->siswa->jenjang ?? '' }}
                        Kelas {{ $pembayaran->siswa->kelas ?? '' }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div style="color:var(--ink-muted);font-size:.78rem;font-weight:600;
                                text-transform:uppercase;letter-spacing:.5px;">Bulan Dibayar</div>
                    <div class="fw-600 mt-1" style="color:var(--ink);">{{ $pembayaran->bulan_label }}</div>
                    <div style="color:var(--ink-muted);font-size:.8rem;">
                        {{ $pembayaran->jumlah_bulan }} bulan
                    </div>
                </div>
            </div>

            {{-- Rincian biaya --}}
            <div class="rounded-3 p-3 mt-3"
                 style="background:var(--bg);border:1px solid var(--border);">
                <div class="d-flex flex-wrap gap-3 align-items-center" style="font-size:.85rem;">
                    <div>
                        <span style="color:var(--ink-muted);">SPP</span>
                        <div class="fw-600" style="color:var(--navy);">
                            Rp {{ number_format($pembayaran->nominal_per_bulan, 0, ',', '.') }}/bln
                            × {{ $pembayaran->jumlah_bulan }}
                        </div>
                    </div>
                    <span class="fw-bold" style="color:var(--red);">−</span>
                    <div>
                        <span style="color:var(--ink-muted);">Donatur</span>
                        <div class="fw-600" style="color:var(--red);" id="previewDonatur">
                            Rp {{ number_format($pembayaran->nominal_donator / max($pembayaran->jumlah_bulan,1), 0, ',', '.') }}/bln
                        </div>
                    </div>
                    @if($pembayaran->nominal_mamin > 0)
                    <span class="fw-bold" style="color:var(--green);">+</span>
                    <div>
                        <span style="color:var(--ink-muted);">Mamin</span>
                        <div class="fw-600" style="color:#0369a1;">
                            Rp {{ number_format($pembayaran->nominal_mamin / max($pembayaran->jumlah_bulan,1), 0, ',', '.') }}/bln
                        </div>
                    </div>
                    @endif
                    <span class="fw-bold" style="color:var(--ink-muted);">=</span>
                    <div>
                        <span style="color:var(--ink-muted);">Total</span>
                        <div class="fw-bold" style="color:var(--green);font-size:1rem;" id="previewTotal">
                            Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
                <div class="mt-2" style="font-size:.72rem;color:var(--ink-muted);">
                    <i class="bi bi-info-circle me-1"></i>
                    Rumus: (SPP − Donatur + Mamin) × {{ $pembayaran->jumlah_bulan }} bulan
                </div>
            </div>
        </div>
    </div>

    {{-- Form edit --}}
    <div class="card mb-4">
        <div class="card-header"
             style="background: var(--navy); border-radius: var(--r-xl) var(--r-xl) 0 0 !important;">
            <h6 class="mb-0 fw-bold" style="color:#fff;">
                <i class="bi bi-pencil me-2"></i>Edit Data
            </h6>
        </div>
        <div class="card-body">

            <form method="POST" action="{{ route('pembayaran.update', $pembayaran) }}">
            @csrf
            @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_bayar"
                           value="{{ old('tanggal_bayar', $pembayaran->tanggal_bayar->format('Y-m-d')) }}"
                           class="form-control @error('tanggal_bayar') is-invalid @enderror" required>
                    @error('tanggal_bayar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Donatur / Bulan
                        <small style="color:var(--ink-muted);font-weight:400;">(akan dihitung ulang)</small>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="nominal_donator" id="inputDonatur"
                               value="{{ old('nominal_donator', (int) ($pembayaran->nominal_donator / max($pembayaran->jumlah_bulan, 1))) }}"
                               class="form-control @error('nominal_donator') is-invalid @enderror"
                               min="0" step="1000">
                        <span class="input-group-text" style="color:var(--ink-muted);font-size:.82rem;">/bln</span>
                    </div>
                    @error('nominal_donator')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Preview total baru --}}
                <div class="rounded-3 p-3 mb-4"
                     style="background:#f0fdf4;border:1px solid #bbf7d0;">
                    <div class="d-flex justify-content-between mb-1" style="font-size:.85rem;">
                        <span style="color:var(--ink-muted);">
                            SPP ({{ $pembayaran->jumlah_bulan }} bln):
                        </span>
                        <strong style="color:var(--ink-soft);">
                            Rp {{ number_format($pembayaran->nominal_per_bulan * $pembayaran->jumlah_bulan, 0, ',', '.') }}
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1" style="font-size:.85rem;color:var(--red);">
                        <span>
                            Donatur (<span id="lblDonaturBln">{{ (int)($pembayaran->nominal_donator / max($pembayaran->jumlah_bulan,1)) }}</span>/bln
                            × {{ $pembayaran->jumlah_bulan }}):
                        </span>
                        <strong id="previewDonaturTotal">
                            −Rp {{ number_format($pembayaran->nominal_donator, 0, ',', '.') }}
                        </strong>
                    </div>
                    @if($pembayaran->nominal_mamin > 0)
                    <div class="d-flex justify-content-between mb-1" style="font-size:.85rem;color:#0369a1;">
                        <span>Mamin ({{ $pembayaran->jumlah_bulan }} bln):</span>
                        <strong>
                            +Rp {{ number_format($pembayaran->nominal_mamin, 0, ',', '.') }}
                        </strong>
                    </div>
                    @endif
                    <hr class="my-2" style="border-color:var(--border);">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold" style="font-size:.85rem;color:var(--ink-soft);">
                            Total Baru:
                        </span>
                        <strong style="color:var(--green);font-size:1.05rem;" id="previewTotalLive">
                            Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}
                        </strong>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Keterangan</label>
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
const sppPerBulan   = {{ $pembayaran->nominal_per_bulan }};
const jumlahBulan   = {{ $pembayaran->jumlah_bulan }};
const maminPerBulan = {{ $pembayaran->jumlah_bulan > 0 ? $pembayaran->nominal_mamin / $pembayaran->jumlah_bulan : 0 }};

document.getElementById('inputDonatur').addEventListener('input', function () {
    const donaturPerBulan = parseFloat(this.value) || 0;
    const donaturTotal    = donaturPerBulan * jumlahBulan;
    const total = (sppPerBulan - donaturPerBulan + maminPerBulan) * jumlahBulan;

    const fmt = n => new Intl.NumberFormat('id-ID').format(Math.round(n));

    document.getElementById('lblDonaturBln').textContent       = fmt(donaturPerBulan);
    document.getElementById('previewDonaturTotal').textContent = '−Rp ' + fmt(donaturTotal);
    document.getElementById('previewTotalLive').textContent    = 'Rp ' + fmt(total);
});
</script>
@endpush