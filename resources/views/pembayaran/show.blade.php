{{-- resources/views/pembayaran/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail Pembayaran')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('pembayaran.index') }}">Pembayaran</a></li>
    <li class="breadcrumb-item active">{{ $pembayaran->kode_bayar }}</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--navy); font-family:'Sora',sans-serif;">
            Detail Pembayaran
        </h4>
        <p class="mb-0" style="color:var(--ink-muted);font-size:.85rem;">
            <code style="color:var(--navy);font-size:.8rem;">{{ $pembayaran->kode_bayar }}</code>
        </p>
    </div>
    <div class="d-flex gap-2">
        @if(!$pembayaran->setoran_id)
        <a href="{{ route('pembayaran.edit', $pembayaran) }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        @endif
        <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center"
         style="background: var(--navy); border-radius: var(--r-xl) var(--r-xl) 0 0 !important;">
        <h6 class="mb-0 fw-bold" style="color:#fff;">
            <i class="bi bi-receipt me-2"></i>{{ $pembayaran->kode_bayar }}
        </h6>
        <span class="badge"
              style="background:var(--green);color:#fff;font-size:.75rem;font-weight:600;
                     padding:.35rem .8rem;border-radius:var(--r-pill);">
            {{ strtoupper($pembayaran->status) }}
        </span>
    </div>
    <div class="card-body p-0">
        <table class="table table-borderless mb-0" style="font-size:.875rem;">
            <tbody>
                <tr>
                    <td class="ps-4 py-3 fw-600" style="width:165px;color:var(--ink-muted);">
                        Tanggal Bayar
                    </td>
                    <td class="py-3 pe-4" style="color:var(--ink-soft);">
                        {{ $pembayaran->tanggal_bayar->isoFormat('D MMMM Y') }}
                    </td>
                </tr>
                <tr style="background:var(--bg);">
                    <td class="ps-4 py-3 fw-600" style="color:var(--ink-muted);">Siswa</td>
                    <td class="py-3 pe-4">
                        <a href="{{ route('siswa.show', $pembayaran->siswa) }}"
                           class="fw-600 text-decoration-none"
                           style="color:var(--navy);">
                            {{ $pembayaran->siswa->nama }}
                        </a>
                        @php $jClass = 'badge-' . strtolower($pembayaran->siswa->jenjang); @endphp
                        <span class="{{ $jClass }} ms-1">
                            {{ $pembayaran->siswa->jenjang }} {{ $pembayaran->siswa->kelas }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="ps-4 py-3 fw-600" style="color:var(--ink-muted);">Bulan Dibayar</td>
                    <td class="py-3 pe-4" style="color:var(--ink-soft);">
                        <strong style="color:var(--ink);">{{ $pembayaran->bulan_label }}</strong>
                        <span style="color:var(--ink-muted);">({{ $pembayaran->jumlah_bulan }} bulan)</span>
                    </td>
                </tr>

                {{-- Rincian biaya --}}
                <tr style="background:var(--bg);">
                    <td class="ps-4 py-3 fw-600" style="color:var(--ink-muted);">SPP</td>
                    <td class="py-3 pe-4" style="color:var(--ink-soft);">
                        Rp {{ number_format($pembayaran->nominal_per_bulan, 0, ',', '.') }}/bln
                        × {{ $pembayaran->jumlah_bulan }}
                        <span class="ms-2 fw-600" style="color:var(--navy);">
                            = Rp {{ number_format($pembayaran->nominal_per_bulan * $pembayaran->jumlah_bulan, 0, ',', '.') }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="ps-4 py-3 fw-600" style="color:var(--ink-muted);">Donatur</td>
                    <td class="py-3 pe-4" style="color:var(--red);">
                        −Rp {{ number_format($pembayaran->nominal_donator * $pembayaran->jumlah_bulan, 0, ',', '.') }}
                    </td>
                </tr>
                @if($pembayaran->nominal_mamin > 0)
                <tr style="background:var(--bg);">
                    <td class="ps-4 py-3 fw-600" style="color:var(--ink-muted);">Mamin</td>
                    <td class="py-3 pe-4" style="color:#0369a1;">
                        +Rp {{ number_format($pembayaran->nominal_mamin * $pembayaran->jumlah_bulan, 0, ',', '.') }}
                    </td>
                </tr>
                @endif
                @if(($pembayaran->kredit_digunakan ?? 0) > 0)
                <tr>
                    <td class="ps-4 py-3 fw-600" style="color:var(--ink-muted);">Kredit Digunakan</td>
                    <td class="py-3 pe-4 fw-600" style="color:var(--green);">
                        −Rp {{ number_format($pembayaran->kredit_digunakan, 0, ',', '.') }}
                    </td>
                </tr>
                @endif

                {{-- Total --}}
                <tr style="border-top: 2px solid var(--border);">
                    <td class="ps-4 py-3 fw-bold" style="color:var(--ink);font-size:.95rem;">
                        TOTAL BAYAR
                    </td>
                    <td class="py-3 pe-4 fw-bold" style="color:var(--green);font-size:1.2rem;">
                        Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}
                    </td>
                </tr>

                <tr style="background:var(--bg);">
                    <td class="ps-4 py-3 fw-600" style="color:var(--ink-muted);">Petugas</td>
                    <td class="py-3 pe-4" style="color:var(--ink-soft);">
                        {{ $pembayaran->user->nama_lengkap ?? $pembayaran->user->name ?? '—' }}
                    </td>
                </tr>

                @if($pembayaran->setoran)
                <tr>
                    <td class="ps-4 py-3 fw-600" style="color:var(--ink-muted);">Setoran</td>
                    <td class="py-3 pe-4">
                        <a href="{{ route('setoran.show', $pembayaran->setoran) }}"
                           class="fw-600 text-decoration-none"
                           style="color:var(--blue);">
                            {{ $pembayaran->setoran->kode_setoran }}
                        </a>
                    </td>
                </tr>
                @endif

                @if($pembayaran->keterangan)
                <tr style="background:var(--bg);">
                    <td class="ps-4 py-3 fw-600" style="color:var(--ink-muted);">Keterangan</td>
                    <td class="py-3 pe-4" style="color:var(--ink-soft);">{{ $pembayaran->keterangan }}</td>
                </tr>
                @endif

            </tbody>
        </table>
    </div>

    @if(!$pembayaran->setoran_id)
    <div class="card-footer d-flex justify-content-end gap-2"
         style="background:var(--bg);border-top:1px solid var(--border);">
        <form method="POST" action="{{ route('pembayaran.destroy', $pembayaran) }}"
              onsubmit="return confirm('Yakin hapus pembayaran {{ $pembayaran->kode_bayar }}?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash me-1"></i>Hapus
            </button>
        </form>
    </div>
    @endif
</div>
</div>
</div>

@endsection