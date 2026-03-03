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
        <h4 class="fw-bold mb-1" style="color: var(--navy); font-family:'Sora',sans-serif;">
            {{ $siswa->nama }}
        </h4>
        <p class="mb-0" style="font-size:.85rem; color:var(--ink-muted);">
            @php $jClass = 'badge-' . strtolower($siswa->jenjang); @endphp
            <span class="{{ $jClass }}">{{ $siswa->jenjang }}</span>
            <span class="mx-1">·</span>Kelas {{ $siswa->kelas }}
            <span class="mx-1">·</span>
            <code style="font-size:.8rem; color:var(--ink-soft);">{{ $siswa->id_siswa }}</code>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('pembayaran.create', ['siswa_id' => $siswa->id]) }}"
           class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Bayar SPP
        </a>
        <a href="{{ route('kredit.create', $siswa) }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-piggy-bank me-1"></i>Kredit
            @if($siswa->saldo_kredit > 0)
            <span class="badge ms-1"
                  style="background:var(--green);color:#fff;font-size:.68rem;font-weight:600;">
                Rp {{ number_format($siswa->saldo_kredit, 0, ',', '.') }}
            </span>
            @endif
        </a>
        <a href="{{ route('siswa.edit', $siswa) }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

{{-- Saldo kredit banner --}}
@if($siswa->saldo_kredit > 0)
<div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3"
     style="background:#f0fdf4; border:1px solid #86efac;">
    <i class="bi bi-piggy-bank-fill fs-4 flex-shrink-0" style="color:var(--green);"></i>
    <div class="flex-grow-1">
        <div class="fw-600" style="color:#065F46; font-size:.9rem;">
            Saldo Kredit: Rp {{ number_format($siswa->saldo_kredit, 0, ',', '.') }}
        </div>
        <div style="color:var(--ink-muted); font-size:.8rem;">
            Akan dipotong otomatis saat pembayaran SPP berikutnya.
        </div>
    </div>
    <a href="{{ route('kredit.create', $siswa) }}" class="btn btn-sm btn-outline-success flex-shrink-0">
        <i class="bi bi-clock-history me-1"></i>Lihat Riwayat
    </a>
</div>
@endif

<div class="row g-3">
    <!-- ─── Info Siswa ─── -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"
                 style="background: var(--navy); color: #fff; border-radius: var(--r-xl) var(--r-xl) 0 0 !important;">
                <h6 class="mb-0 fw-bold" style="color:#fff;">
                    <i class="bi bi-person-circle me-2"></i>Informasi Siswa
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0" style="font-size:.865rem;">
                    @php
                        $rows = [
                            ['ID Siswa',    '<code style="font-size:.8rem;color:var(--navy);">' . e($siswa->id_siswa) . '</code>'],
                            ['Nama',        '<span class="fw-600" style="color:var(--ink);">' . e($siswa->nama) . '</span>'],
                        ];
                    @endphp
                    <tbody>
                        <tr>
                            <td class="ps-4 py-2" style="color:var(--ink-muted);width:120px;font-weight:600;font-size:.78rem;">
                                ID Siswa
                            </td>
                            <td class="py-2 pe-4">
                                <code style="font-size:.8rem;color:var(--navy);">{{ $siswa->id_siswa }}</code>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">Nama</td>
                            <td class="py-2 pe-4 fw-600" style="color:var(--ink);">{{ $siswa->nama }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">Jenjang</td>
                            <td class="py-2 pe-4">
                                <span class="badge-{{ strtolower($siswa->jenjang) }}">{{ $siswa->jenjang }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">Kelas</td>
                            <td class="py-2 pe-4" style="color:var(--ink-soft);">{{ $siswa->kelas }}</td>
                        </tr>
                        <tr style="background:var(--bg);">
                            <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">SPP/Bulan</td>
                            <td class="py-2 pe-4 fw-600" style="color:var(--navy);">
                                Rp {{ number_format($siswa->nominal_pembayaran, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">Donatur/Bln</td>
                            <td class="py-2 pe-4" style="color:var(--red);">
                                −Rp {{ number_format($siswa->nominal_donator, 0, ',', '.') }}
                            </td>
                        </tr>
                        @if($siswa->jenjang === 'TK')
                        <tr>
                            <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">Mamin/Bln</td>
                            <td class="py-2 pe-4" style="color:#0369a1;">
                                +Rp {{ number_format($siswa->nominal_mamin, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif
                        <tr style="background:var(--bg);">
                            <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">Total/Bulan</td>
                            <td class="py-2 pe-4 fw-600" style="color:var(--green);font-size:.95rem;">
                                Rp {{ number_format($siswa->total_tagihan, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">Saldo Kredit</td>
                            <td class="py-2 pe-4">
                                @if($siswa->saldo_kredit > 0)
                                    <span class="fw-600" style="color:var(--green);">
                                        Rp {{ number_format($siswa->saldo_kredit, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span style="color:var(--ink-faint);">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">Masuk</td>
                            <td class="py-2 pe-4" style="color:var(--ink-soft);">
                                {{ $siswa->tanggal_masuk->isoFormat('D MMM Y') }}
                            </td>
                        </tr>
                        @if($siswa->tanggal_keluar)
                        <tr>
                            <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">Keluar</td>
                            <td class="py-2 pe-4" style="color:var(--red);">
                                {{ $siswa->tanggal_keluar->isoFormat('D MMM Y') }}
                            </td>
                        </tr>
                        @endif
                        <tr style="background:var(--bg);">
                            <td class="ps-4 py-2 pb-3" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">Status</td>
                            <td class="py-2 pb-3 pe-4">
                                @if($siswa->status === 'aktif')
                                    <span class="badge"
                                          style="background:#d1fae5;color:#065F46;border:1px solid #6EE7B7;
                                                 font-size:.72rem;font-weight:600;padding:.3rem .8rem;">
                                        <i class="bi bi-check-circle me-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="badge"
                                          style="background:#f1f5f9;color:#64748B;border:1px solid #e2e8f0;
                                                 font-size:.72rem;font-weight:600;padding:.3rem .8rem;">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ─── Status Pembayaran Per Bulan ─── -->
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-calendar3 me-2" style="color:var(--blue);"></i>
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
                    $pct        = $totalBulan > 0 ? round($sudahBayar / $totalBulan * 100) : 0;
                @endphp

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="progress flex-grow-1" style="height:10px;border-radius:99px;background:var(--border);">
                        <div class="progress-bar"
                             style="width:{{ $pct }}%;background:var(--green);border-radius:99px;">
                        </div>
                    </div>
                    <span class="fw-600" style="color:var(--green);font-size:.82rem;white-space:nowrap;">
                        {{ $sudahBayar }}/{{ $totalBulan }} bulan
                    </span>
                </div>

                <div class="row g-2">
                    @foreach($statusBulan as $bs)
                    <div class="col-6 col-md-3">
                        @if($bs['sudah_bayar'])
                        <div class="rounded-3 p-2 text-center"
                             style="background:#dcfce7;border:1px solid #86efac;cursor:default"
                             title="Dibayar: {{ $bs['data_bayar']?->tanggal_bayar?->format('d/m/Y') ?? '-' }}">
                            <div class="fw-600" style="font-size:.8rem;color:#15803d;">{{ $bs['nama_bulan'] }}</div>
                            <i class="bi bi-check-circle-fill" style="color:#16a34a;"></i>
                            <div style="font-size:.7rem;color:#15803d;">
                                @if($bs['data_bayar'])
                                Rp {{ number_format(($bs['data_bayar']->total_bayar / max($bs['data_bayar']->jumlah_bulan, 1)), 0, ',', '.') }}
                                @else
                                Rp {{ number_format($siswa->total_tagihan ?? 0, 0, ',', '.') }}
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="rounded-3 p-2 text-center"
                             style="background:#fff7ed;border:1px solid #fed7aa;">
                            <div class="fw-600" style="font-size:.8rem;color:#c2410c;">{{ $bs['nama_bulan'] }}</div>
                            <i class="bi bi-clock" style="color:#f97316;"></i>
                            <div style="font-size:.7rem;color:#c2410c;">Belum</div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                @if(count($statusBulan) === 0)
                <p class="text-center py-3" style="color:var(--ink-muted);">
                    Tidak ada bulan aktif untuk siswa ini.
                </p>
                @endif
            </div>
        </div>

        <!-- ─── Riwayat Pembayaran ─── -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-clock-history me-2" style="color:var(--blue);"></i>Riwayat Pembayaran
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0" style="font-size:.85rem;">
                    <thead>
                        <tr>
                            <th class="ps-4">Kode</th>
                            <th>Tanggal</th>
                            <th>Bulan</th>
                            <th class="text-end">Kredit</th>
                            <th class="text-end">Total Bayar</th>
                            <th>Petugas</th>
                            <th class="pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswa->pembayaran as $p)
                        <tr>
                            <td class="ps-4">
                                <code class="small" style="color:var(--navy);">{{ $p->kode_bayar }}</code>
                            </td>
                            <td class="small" style="color:var(--ink-soft);">
                                {{ $p->tanggal_bayar->format('d/m/Y') }}
                            </td>
                            <td class="small" style="color:var(--ink-soft);">{{ $p->bulan_label }}</td>
                            <td class="text-end small">
                                @if(($p->kredit_digunakan ?? 0) > 0)
                                <span class="fw-600" style="color:var(--green);">
                                    −Rp {{ number_format($p->kredit_digunakan, 0, ',', '.') }}
                                </span>
                                @else
                                <span style="color:var(--ink-faint);">—</span>
                                @endif
                            </td>
                            <td class="text-end fw-600" style="color:var(--green);">
                                Rp {{ number_format($p->total_bayar, 0, ',', '.') }}
                            </td>
                            <td class="small" style="color:var(--ink-muted);">
                                {{ $p->user->name ?? '—' }}
                            </td>
                            <td class="pe-4">
                                <a href="{{ route('pembayaran.show', $p) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox d-block fs-2 mb-2" style="color:var(--ink-faint);"></i>
                                <span style="color:var(--ink-muted);">Belum ada riwayat pembayaran.</span>
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