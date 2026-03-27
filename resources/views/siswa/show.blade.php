{{-- resources/views/siswa/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail Siswa - ' . $siswa->nama)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.index') }}">Data Siswa</a></li>
    <li class="breadcrumb-item active">{{ $siswa->nama }}</li>
@endsection

@section('content')

    @php
        // $kelasAktif sudah dikirim controller (SiswaKelas model | null)
        $ka = $kelasAktif;
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--navy); font-family:'Sora',sans-serif;">
                {{ $siswa->nama }}
            </h4>
            <p class="mb-0" style="font-size:.85rem; color:var(--ink-muted);">
                <span class="badge-{{ strtolower($siswa->jenjang) }}">Kelas {{ $ka->kelas->nama }}</span>
                <span class="mx-1">·</span>
                <code style="font-size:.8rem; color:var(--ink-soft);">{{ $siswa->id_siswa }}</code>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
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


    {{-- Warning jika belum ada kelas aktif --}}
    @if (!$ka)
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                Siswa ini belum ditempatkan di kelas untuk tahun ajaran aktif.
                <a href="{{ route('siswa.edit', $siswa) }}" class="alert-link ms-1">Atur sekarang →</a>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════
     BARIS 1 : Info Siswa  |  Status Pembayaran
     ═══════════════════════════════════════════════════════ --}}
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
                        <tbody>
                            <tr>
                                <td class="ps-4 py-2"
                                    style="color:var(--ink-muted);width:120px;font-weight:600;font-size:.78rem;">ID Siswa
                                </td>
                                <td class="py-2 pe-4">
                                    <code style="font-size:.8rem;color:var(--navy);">{{ $siswa->id_siswa }}</code>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">Nama
                                </td>
                                <td class="py-2 pe-4 fw-600" style="color:var(--ink);">{{ $siswa->nama }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">Kelas
                                </td>
                                <td class="py-2 pe-4" style="color:var(--ink-soft);">
                                    {{ $ka?->kelas?->nama ?? '—' }}
                                </td>
                            </tr>
                            <tr style="background:var(--bg);">
                                <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">
                                    SPP/Bulan</td>
                                <td class="py-2 pe-4 fw-600" style="color:var(--navy);">
                                    @if ($ka)
                                        Rp {{ number_format($ka->nominal_spp, 0, ',', '.') }}
                                    @else
                                        <span style="color:var(--ink-faint);">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">
                                    Donatur/Bln</td>
                                <td class="py-2 pe-4" style="color:var(--red);">
                                    @if ($ka && $ka->nominal_donator > 0)
                                        −Rp {{ number_format($ka->nominal_donator, 0, ',', '.') }}
                                    @else
                                        <span style="color:var(--ink-faint);">—</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($siswa->jenjang === 'TK')
                                <tr>
                                    <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">
                                        Mamin/Bln</td>
                                    <td class="py-2 pe-4" style="color:#0369a1;">
                                        @if ($ka && $ka->nominal_mamin > 0)
                                            +Rp {{ number_format($ka->nominal_mamin, 0, ',', '.') }}
                                        @else
                                            <span style="color:var(--ink-faint);">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            <tr style="background:var(--bg);">
                                <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">
                                    Total/Bulan</td>
                                <td class="py-2 pe-4 fw-600" style="color:var(--green);font-size:.95rem;">
                                    @if ($ka)
                                        Rp {{ number_format($ka->getTagihanPerBulan(), 0, ',', '.') }}
                                    @else
                                        <span style="color:var(--ink-faint);">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">Saldo
                                    Kredit</td>
                                <td class="py-2 pe-4">
                                    @if ($siswa->saldo_kredit > 0)
                                        <span class="fw-600" style="color:var(--green);">
                                            Rp {{ number_format($siswa->saldo_kredit, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span style="color:var(--ink-faint);">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">Masuk
                                </td>
                                <td class="py-2 pe-4" style="color:var(--ink-soft);">
                                    {{ $siswa->tanggal_masuk->isoFormat('D MMM Y') }}
                                </td>
                            </tr>
                            @if ($siswa->tanggal_keluar)
                                <tr>
                                    <td class="ps-4 py-2" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">
                                        Keluar</td>
                                    <td class="py-2 pe-4" style="color:var(--red);">
                                        {{ $siswa->tanggal_keluar->isoFormat('D MMM Y') }}
                                    </td>
                                </tr>
                            @endif
                            <tr style="background:var(--bg);">
                                <td class="ps-4 py-2 pb-3" style="color:var(--ink-muted);font-weight:600;font-size:.78rem;">
                                    Status</td>
                                <td class="py-2 pb-3 pe-4">
                                    @if ($siswa->status === 'aktif')
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
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-calendar3 me-2" style="color:var(--blue);"></i>
                        Status Pembayaran
                    </h6>
                    <a href="{{ route('cetak.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-printer me-1"></i>Cetak Kartu
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $sudahBayar = collect($statusBulan)->where('sudah_bayar', true)->count();
                        $totalBulan = count($statusBulan);
                        $pct = $totalBulan > 0 ? round(($sudahBayar / $totalBulan) * 100) : 0;
                    @endphp

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="progress flex-grow-1"
                            style="height:10px;border-radius:99px;background:var(--border);">
                            <div class="progress-bar"
                                style="width:{{ $pct }}%;background:var(--green);border-radius:99px;">
                            </div>
                        </div>
                        <span class="fw-600" style="color:var(--green);font-size:.82rem;white-space:nowrap;">
                            {{ $sudahBayar }}/{{ $totalBulan }} bulan
                        </span>
                    </div>

                    {{--
                        Pra-hitung distribusi kredit per bulan untuk seluruh $statusBulan.
                        Kredit dari setiap transaksi dialokasikan bertahap: bulan pertama
                        diserap dulu, sisa lanjut ke bulan berikutnya, dst sampai habis.
                        Hasilnya disimpan di $kreditPerBulan['2024-07'] = 30000, dst.
                    --}}
                    @php
                        $kreditPerBulan = [];   // ['2024-07' => nominal_kredit_bulan_ini]

                        // Kelompokkan bulan per transaksi pembayaran
                        $transaksiMap = [];
                        foreach ($statusBulan as $bs) {
                            if (!$bs['sudah_bayar'] || !$bs['data_bayar']) continue;
                            $pid = $bs['data_bayar']->id;
                            if (!isset($transaksiMap[$pid])) {
                                $transaksiMap[$pid] = [
                                    'bayar'  => $bs['data_bayar'],
                                    'bulan'  => [],
                                ];
                            }
                            $transaksiMap[$pid]['bulan'][] = $bs['bulan'];
                        }

                        foreach ($transaksiMap as $entry) {
                            $bayar  = $entry['bayar'];
                            $bulan  = $entry['bulan'];
                            sort($bulan); // urut kronologis

                            $jmlBln          = max(1, (int) $bayar->jumlah_bulan);
                            $donaturPerBulan = round((float) $bayar->nominal_donator / $jmlBln, 0);
                            $maminPerBulan   = round((float) $bayar->nominal_mamin   / $jmlBln, 0);
                            $tagihanBersih   = max(0, (float) $bayar->nominal_per_bulan - $donaturPerBulan + $maminPerBulan);
                            $sisaKredit      = (float) ($bayar->kredit_digunakan ?? 0);

                            foreach ($bulan as $b) {
                                $potongan            = min($sisaKredit, $tagihanBersih);
                                $kreditPerBulan[$b]  = $potongan;
                                $sisaKredit         -= $potongan;
                            }
                        }
                    @endphp

                    <div class="row g-2">
                        @foreach ($statusBulan as $bs)
                            <div class="col-6 col-md-3">
                                @if ($bs['sudah_bayar'])
                                    @php
                                        $bayar           = $bs['data_bayar'];
                                        $kreditBulanIni  = $kreditPerBulan[$bs['bulan']] ?? 0;
                                        if ($bayar) {
                                            $jmlBln          = max(1, (int) $bayar->jumlah_bulan);
                                            $donaturPerBulan = round((float) $bayar->nominal_donator / $jmlBln, 0);
                                            $maminPerBulan   = round((float) $bayar->nominal_mamin   / $jmlBln, 0);
                                            $tagihanBersih   = max(0, (float) $bayar->nominal_per_bulan - $donaturPerBulan + $maminPerBulan);
                                            $tampil          = max(0, $tagihanBersih - $kreditBulanIni);
                                        } else {
                                            $tampil = $ka ? $ka->getTagihanPerBulan() : 0;
                                        }
                                    @endphp
                                    <div class="rounded-3 p-2 text-center"
                                        style="background:#dcfce7;border:1px solid #86efac;cursor:default"
                                        title="Dibayar: {{ $bayar?->tanggal_bayar?->format('d/m/Y') ?? '-' }}">
                                        <div class="fw-600" style="font-size:.8rem;color:#15803d;">
                                            {{ $bs['nama_bulan'] }}</div>
                                        <i class="bi bi-check-circle-fill" style="color:#16a34a;"></i>
                                        <div style="font-size:.7rem;color:#15803d;">
                                            Rp {{ number_format($tampil, 0, ',', '.') }}
                                            @if ($kreditBulanIni > 0)
                                                <br><span style="color:#059669;opacity:.75;font-size:.65rem;">
                                                    kredit −{{ number_format($kreditBulanIni, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="rounded-3 p-2 text-center"
                                        style="background:#fff7ed;border:1px solid #fed7aa;">
                                        <div class="fw-600" style="font-size:.8rem;color:#c2410c;">
                                            {{ $bs['nama_bulan'] }}</div>
                                        <i class="bi bi-clock" style="color:#f97316;"></i>
                                        <div style="font-size:.7rem;color:#c2410c;">Belum</div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if (count($statusBulan) === 0)
                        <p class="text-center py-3" style="color:var(--ink-muted);">
                            Tidak ada bulan aktif untuk siswa ini.
                        </p>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- end baris 1 --}}

    {{-- ═══════════════════════════════════════════════════════
     BARIS 2 : Riwayat Pembayaran (full width)
     ═══════════════════════════════════════════════════════ --}}
    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
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
                                        @if (($p->kredit_digunakan ?? 0) > 0)
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
    </div>{{-- end baris 2 --}}

@endsection