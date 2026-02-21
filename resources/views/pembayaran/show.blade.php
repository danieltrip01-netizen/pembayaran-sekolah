{{-- resources/views/pembayaran/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail Pembayaran')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0" style="color: var(--primary)">Detail Pembayaran</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('pembayaran.edit', $pembayaran) }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card">
    <div class="card-header py-3" style="background:var(--primary);color:white">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">{{ $pembayaran->kode_bayar }}</h6>
            <span class="badge bg-success">{{ strtoupper($pembayaran->status) }}</span>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-borderless">
            <tr>
                <td class="text-muted fw-600" style="width:160px">Tanggal Bayar</td>
                <td>{{ $pembayaran->tanggal_bayar->isoFormat('D MMMM Y') }}</td>
            </tr>
            <tr>
                <td class="text-muted fw-600">Siswa</td>
                <td>
                    <a href="{{ route('siswa.show', $pembayaran->siswa) }}" class="fw-bold text-decoration-none">
                        {{ $pembayaran->siswa->nama }}
                    </a>
                    <span class="badge badge-{{ strtolower($pembayaran->siswa->jenjang) }} ms-1">
                        {{ $pembayaran->siswa->jenjang }} {{ $pembayaran->siswa->kelas }}
                    </span>
                </td>
            </tr>
            <tr>
                <td class="text-muted fw-600">Bulan Dibayar</td>
                <td><strong>{{ $pembayaran->bulan_label }}</strong> ({{ $pembayaran->jumlah_bulan }} bulan)</td>
            </tr>
            <tr>
                <td class="text-muted fw-600">SPP</td>
                <td>Rp {{ number_format($pembayaran->nominal_per_bulan * $pembayaran->jumlah_bulan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-muted fw-600">Donatur</td>
                <td>Rp {{ number_format($pembayaran->nominal_donator * $pembayaran->jumlah_bulan, 0, ',', '.') }}</td>
            </tr>
            @if($pembayaran->nominal_mamin > 0)
            <tr>
                <td class="text-muted fw-600">Mamin</td>
                <td class="text-info">Rp {{ number_format($pembayaran->nominal_mamin * $pembayaran->jumlah_bulan, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr style="border-top: 2px solid #e2e8f0">
                <td class="fw-bold fs-6">TOTAL BAYAR</td>
                <td class="fw-bold fs-5 text-primary">Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-muted fw-600">Petugas</td>
                <td>{{ $pembayaran->user->nama_lengkap ?? $pembayaran->user->name ?? '-' }}</td>
            </tr>
            @if($pembayaran->setoran)
            <tr>
                <td class="text-muted fw-600">Setoran</td>
                <td>
                    <a href="{{ route('setoran.show', $pembayaran->setoran) }}">
                        {{ $pembayaran->setoran->kode_setoran }}
                    </a>
                </td>
            </tr>
            @endif
            @if($pembayaran->keterangan)
            <tr>
                <td class="text-muted fw-600">Keterangan</td>
                <td>{{ $pembayaran->keterangan }}</td>
            </tr>
            @endif
        </table>
    </div>
</div>
</div>
</div>
@endsection