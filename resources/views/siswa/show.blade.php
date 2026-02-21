{{-- resources/views/siswa/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail Siswa - ' . $siswa->nama)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.index') }}">Data Siswa</a></li>
    <li class="breadcrumb-item active">{{ $siswa->nama }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">{{ $siswa->nama }}</h4>
        <p class="text-muted small mb-0">
            <span class="badge badge-{{ strtolower($siswa->jenjang) }}">{{ $siswa->jenjang }}</span>
            Kelas {{ $siswa->kelas }} | {{ $siswa->id_siswa }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pembayaran.create', ['siswa_id' => $siswa->id]) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Bayar SPP
        </a>
        <a href="{{ route('siswa.edit', $siswa) }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<div class="row g-3">
    <!-- Info Siswa -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header py-3" style="background: var(--primary); color:white">
                <h6 class="mb-0 fw-bold"><i class="bi bi-person-circle me-2"></i>Informasi Siswa</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted fw-600" style="width:110px">ID Siswa</td>
                        <td><code>{{ $siswa->id_siswa }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-600">Nama</td>
                        <td class="fw-bold">{{ $siswa->nama }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-600">Jenjang</td>
                        <td><span class="badge badge-{{ strtolower($siswa->jenjang) }}">{{ $siswa->jenjang }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-600">Kelas</td>
                        <td>{{ $siswa->kelas }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-600">SPP/Bulan</td>
                        <td class="fw-bold text-primary">Rp {{ number_format($siswa->nominal_pembayaran, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-600">Donatur/Bln</td>
                        <td>Rp {{ number_format($siswa->nominal_donator, 0, ',', '.') }}</td>
                    </tr>
                    @if($siswa->jenjang === 'TK')
                    <tr>
                        <td class="text-muted fw-600">Mamin/Bln</td>
                        <td class="text-info">Rp {{ number_format($siswa->nominal_mamin, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted fw-600">Total/Bulan</td>
                        <td class="fw-bold text-success">Rp {{ number_format($siswa->total_tagihan, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-600">Masuk</td>
                        <td>{{ $siswa->tanggal_masuk->isoFormat('D MMM Y') }}</td>
                    </tr>
                    @if($siswa->tanggal_keluar)
                    <tr>
                        <td class="text-muted fw-600">Keluar</td>
                        <td class="text-danger">{{ $siswa->tanggal_keluar->isoFormat('D MMM Y') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted fw-600">Status</td>
                        <td>
                            @if($siswa->status === 'aktif')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i>Aktif
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Tidak Aktif</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Status Pembayaran Per Bulan -->
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-calendar3 me-2" style="color:var(--primary)"></i>
                    Status Pembayaran T.A. {{ $tahunAjaran }}/{{ $tahunAjaran + 1 }}
                </h6>
                <a href="{{ route('cetak.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i>Cetak Kartu
                </a>
            </div>
            <div class="card-body">
                @php
                    $sudahBayar = collect($statusBulan)->where('sudah_bayar', true)->count();
                    $totalBulan = count($statusBulan);
                @endphp

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="progress flex-grow-1" style="height:10px;border-radius:99px">
                        <div class="progress-bar bg-success"
                             style="width: {{ $totalBulan > 0 ? ($sudahBayar / $totalBulan * 100) : 0 }}%">
                        </div>
                    </div>
                    <span class="small fw-600 text-success">{{ $sudahBayar }}/{{ $totalBulan }} bulan</span>
                </div>

                <div class="row g-2">
                    @foreach($statusBulan as $bs)
                    <div class="col-6 col-md-3">
                        @if($bs['sudah_bayar'])
                        <div class="rounded-3 p-2 text-center"
                             style="background:#dcfce7;border:1px solid #86efac;cursor:pointer"
                             title="Dibayar: {{ $bs['data_bayar']?->tanggal_bayar?->format('d/m/Y') ?? '-' }}">
                            <div class="fw-bold" style="font-size:.8rem;color:#16a34a">{{ $bs['nama_bulan'] }}</div>
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <div style="font-size:.7rem;color:#16a34a">
                                Rp {{ number_format($siswa->total_tagihan?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        @else
                        <div class="rounded-3 p-2 text-center"
                             style="background:#fff7ed;border:1px solid #fed7aa">
                            <div class="fw-bold" style="font-size:.8rem;color:#ea580c">{{ $bs['nama_bulan'] }}</div>
                            <i class="bi bi-clock text-warning"></i>
                            <div style="font-size:.7rem;color:#ea580c">Belum</div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                @if(count($statusBulan) === 0)
                <p class="text-muted text-center py-3">Tidak ada bulan aktif untuk siswa ini.</p>
                @endif
            </div>
        </div>

        <!-- Riwayat Pembayaran -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-clock-history me-2" style="color:var(--primary)"></i>Riwayat Pembayaran
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Bulan</th>
                            <th>SPP</th>
                            <th>Total</th>
                            <th>Petugas</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswa->pembayaran as $p)
                        <tr>
                            <td><code class="text-primary small">{{ $p->kode_bayar }}</code></td>
                            <td class="small">{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
                            <td class="small">{{ $p->bulan_label }}</td>
                            <td class="small">Rp {{ number_format($p->nominal_per_bulan * $p->jumlah_bulan, 0, ',', '.') }}</td>
                            <td class="fw-600 text-success">Rp {{ number_format($p->total_bayar, 0, ',', '.') }}</td>
                            <td class="small">{{ $p->user->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('pembayaran.show', $p) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox d-block fs-2 mb-2"></i>Belum ada riwayat pembayaran.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection