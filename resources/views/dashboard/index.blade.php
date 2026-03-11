{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')

@php
    $isAdmin  = !$jenjang;
    $jmlBelum = $siswaBelumBayar->count();
    $pctLunas = $totalSiswa > 0
        ? round((($totalSiswa - $jmlBelum) / $totalSiswa) * 100)
        : 0;
    $progressColor = $pctLunas >= 80 ? 'var(--green)' : 'var(--orange)';
    $badgeLunasVariant = $pctLunas >= 80 ? 'green' : 'red';
@endphp

{{-- ── Header ─────────────────────────────────────────────── --}}
<x-dashboard.page-header
    :isAdmin="$isAdmin"
    :jenjang="$jenjang"
    :tahunPelajaran="$tahunPelajaran"
/>

{{-- ── Banner: tidak ada tahun pelajaran aktif ──────────────── --}}
@if(!$tahunPelajaran)
    <x-dashboard.alert-warning
        title="Tidak ada tahun pelajaran aktif"
        message="Data yang ditampilkan mungkin tidak akurat."
        :linkHref="route('tahun-pelajaran.index')"
        linkLabel="Aktifkan tahun pelajaran →"
    />
@endif


{{-- ════════════════════════════════════════════════════════
     LAYOUT ADMIN YAYASAN
════════════════════════════════════════════════════════ --}}
@if($isAdmin)

{{-- KPI Row ─────────────────────────────────────────────── --}}
<div class="row g-3 mb-3">

    {{-- Total Siswa --}}
    <div class="col-6 col-xl-3">
        <x-dashboard.kpi-card
            icon="people-fill"
            label="Total Siswa Aktif"
            value="{{ number_format($totalSiswa) }}"
            watermark="people-fill"
        >
            <x-slot:badge>
                <span class="kpi-badge kpi-badge--blue">3 Jenjang</span>
            </x-slot:badge>

            <div class="mt-2 d-flex gap-1 flex-wrap">
                <span class="badge-TK">TK {{ $siswaPerJenjang['TK'] ?? 0 }}</span>
                <span class="badge-SD">SD {{ $siswaPerJenjang['SD'] ?? 0 }}</span>
                <span class="badge-SMP">SMP {{ $siswaPerJenjang['SMP'] ?? 0 }}</span>
            </div>
        </x-dashboard.kpi-card>
    </div>

    {{-- Total Pemasukan --}}
    <div class="col-6 col-xl-3">
        <x-dashboard.kpi-card
            icon="bank2"
            iconVariant="yellow"
            label="Total Pemasukan"
            valueSize="md"
            valueVariant="yellow"
            value="Rp {{ number_format($totalPemasukan, 0, ',', '.') }}"
            sub="{{ number_format($totalSetoran) }} setoran tercatat"
            watermark="bank2"
        >
            <x-slot:badge>
                <span class="kpi-badge kpi-badge--yellow">All time</span>
            </x-slot:badge>
        </x-dashboard.kpi-card>
    </div>

    {{-- Pemasukan Bulan Ini --}}
    <div class="col-6 col-xl-3">
        <x-dashboard.kpi-card
            icon="arrow-up-circle-fill"
            iconVariant="green"
            label="Pemasukan Bulan Ini"
            valueSize="md"
            valueVariant="green"
            value="Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}"
            sub="{{ $transaksiHariIni }} transaksi hari ini"
            watermark="cash-stack"
        >
            <x-slot:badge>
                <span class="kpi-badge kpi-badge--green">{{ now()->isoFormat('MMM Y') }}</span>
            </x-slot:badge>
        </x-dashboard.kpi-card>
    </div>

    {{-- Belum Bayar --}}
    <div class="col-6 col-xl-3">
        <x-dashboard.kpi-card
            icon="exclamation-circle-fill"
            iconVariant="red"
            label="Belum Bayar Bulan Ini"
            valueVariant="red"
            value="{{ $jmlBelum }} <span class='text-unit'> siswa</span>"
            watermark="exclamation-circle"
        >
            <x-slot:badge>
                <span class="kpi-badge kpi-badge--{{ $badgeLunasVariant }}">{{ $pctLunas }}% lunas</span>
            </x-slot:badge>

            <div class="mt-2">
                <x-dashboard.progress-bar :percent="$pctLunas" :colorVar="$progressColor" />
                <div class="kpi-sub">{{ $totalSiswa - $jmlBelum }} dari {{ $totalSiswa }} sudah lunas</div>
            </div>
        </x-dashboard.kpi-card>
    </div>

</div>

{{-- Row 2: Grafik (8) + Jenjang Breakdown (4) ────────────── --}}
<div class="row g-3 mb-3">

    <div class="col-lg-8">
        <x-dashboard.card-panel
            title="Grafik Pemasukan — TA {{ $tahunPelajaran?->nama ?? '-' }}"
            icon="graph-up"
            class="h-100"
        >
            <x-slot:headerRight>
                <span class="grafik-total">
                    Total: <strong>Rp {{ number_format(array_sum($grafikData['data']), 0, ',', '.') }}</strong>
                </span>
            </x-slot:headerRight>
            <canvas id="grafikPemasukan" height="95"></canvas>
        </x-dashboard.card-panel>
    </div>

    <div class="col-lg-4">
        <x-dashboard.card-panel title="Pemasukan per Jenjang" icon="bar-chart-fill" class="h-100">
            @php
                $jData = [
                    'TK'  => ['colorVar' => '#F59E0B',      'jumlah' => $pemasukanPerJenjang['TK']  ?? 0],
                    'SD'  => ['colorVar' => 'var(--blue)',  'jumlah' => $pemasukanPerJenjang['SD']  ?? 0],
                    'SMP' => ['colorVar' => 'var(--green)', 'jumlah' => $pemasukanPerJenjang['SMP'] ?? 0],
                ];
                $totalJ = array_sum(array_column($jData, 'jumlah')) ?: 1;
                $maxJ   = max(array_column($jData, 'jumlah')) ?: 1;
            @endphp

            <div class="d-flex flex-column justify-content-between h-100">
                <div>
                    @foreach($jData as $j => $d)
                    <div class="jenjang-row">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge-{{ $j }}">{{ $j }}</span>
                                <span class="kpi-sub">{{ $siswaPerJenjang[$j] ?? 0 }} siswa</span>
                            </div>
                            <span class="jenjang-amount">
                                Rp {{ number_format($d['jumlah'], 0, ',', '.') }}
                            </span>
                        </div>
                        <x-dashboard.progress-bar
                            :percent="round($d['jumlah'] / $maxJ * 100)"
                            :colorVar="$d['colorVar']"
                        />
                        <div class="jenjang-pct">
                            {{ round($d['jumlah'] / $totalJ * 100) }}% dari total
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    <div class="section-label">Aksi Cepat</div>
                    <div class="row g-2">
                        <div class="col-4">
                            <x-dashboard.quick-btn href="{{ route('laporan.index') }}" icon="bar-chart"  iconClass="qb-icon--indigo" label="Laporan" />
                        </div>
                        <div class="col-4">
                            <x-dashboard.quick-btn href="{{ route('setoran.index') }}" icon="wallet2"   iconClass="qb-icon--blue"   label="Setoran" />
                        </div>
                        <div class="col-4">
                            <x-dashboard.quick-btn href="{{ route('siswa.index') }}"  icon="people"    iconClass="qb-icon--navy"   label="Siswa" />
                        </div>
                    </div>
                </div>
            </div>
        </x-dashboard.card-panel>
    </div>

</div>

{{-- Row 3: Belum Bayar (5) + Setoran Terbaru (7) ─────────── --}}
<div class="row g-3">

    <div class="col-lg-5">
        <x-dashboard.card-panel
            title="Belum Bayar {{ now()->isoFormat('MMMM Y') }}"
            icon="exclamation-circle"
            iconClass="text-danger"
            :scrollable="true"
        >
            <x-slot:titleExtra>
                <span class="kpi-badge kpi-badge--red ms-1">{{ $jmlBelum }}</span>
            </x-slot:titleExtra>

            @forelse($siswaBelumBayar as $s)
                <x-dashboard.feed-siswa-item :siswa="$s" :showJenjang="true" />
            @empty
                <x-dashboard.empty-state icon="check-circle-fill" iconVariant="green" message="Semua sudah lunas!" />
            @endforelse
        </x-dashboard.card-panel>
    </div>

    <div class="col-lg-7">
        <x-dashboard.card-panel
            title="Setoran Terbaru"
            icon="wallet2"
            :seeAllHref="route('setoran.index')"
            seeAllLabel="Lihat Semua"
            :noPad="true"
        >
            <div class="table-responsive">
                <table class="table tbl-setoran">
                    <thead>
                        <tr>
                            <th>Kode</th><th>Tanggal</th><th>Jenjang</th>
                            <th class="text-end">Grand Total</th><th>Petugas</th>
                            <th style="width:48px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($setoranTerbaru as $s)
                        <tr>
                            <td class="tbl-setoran__kode">{{ $s->kode_setoran }}</td>
                            <td class="tbl-setoran__date">{{ $s->tanggal_setoran->format('d/m/Y') }}</td>
                            <td><span class="badge-{{ $s->jenjang }}">{{ $s->jenjang }}</span></td>
                            <td class="text-end tbl-setoran__amount">
                                Rp {{ number_format($s->total_keseluruhan, 0, ',', '.') }}
                            </td>
                            <td class="tbl-setoran__user">{{ $s->user->name ?? '—' }}</td>
                            <td>
                                <a href="{{ route('setoran.show', $s) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 tbl-setoran__date">Belum ada setoran.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-dashboard.card-panel>
    </div>

</div>


{{-- ════════════════════════════════════════════════════════
     LAYOUT PETUGAS JENJANG
════════════════════════════════════════════════════════ --}}
@else

{{-- KPI Row ─────────────────────────────────────────────── --}}
<div class="row g-3 mb-3">

    {{-- Siswa Jenjang --}}
    <div class="col-6 col-xl-3">
        <x-dashboard.kpi-card
            icon="people-fill"
            label="Siswa Aktif {{ $jenjang }}"
            value="{{ number_format($totalSiswa) }}"
            watermark="people-fill"
        >
            <x-slot:badge>
                <span class="badge-{{ $jenjang }}">{{ $jenjang }}</span>
            </x-slot:badge>
            <div class="mt-2">
                <x-dashboard.progress-bar :percent="$pctLunas" colorVar="var(--green)" />
                <div class="kpi-sub">{{ $totalSiswa - $jmlBelum }} dari {{ $totalSiswa }} lunas bulan ini</div>
            </div>
        </x-dashboard.kpi-card>
    </div>

    {{-- Pemasukan Bulan Ini --}}
    <div class="col-6 col-xl-3">
        <x-dashboard.kpi-card
            icon="arrow-up-circle-fill"
            iconVariant="green"
            label="Pemasukan Bulan Ini"
            valueSize="md"
            valueVariant="green"
            value="Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}"
            sub="{{ $transaksiHariIni }} transaksi hari ini"
            watermark="cash-stack"
        >
            <x-slot:badge>
                <span class="kpi-badge kpi-badge--green">{{ now()->isoFormat('MMM Y') }}</span>
            </x-slot:badge>
        </x-dashboard.kpi-card>
    </div>

    {{-- Belum Disetor --}}
    <div class="col-6 col-xl-3">
        <x-dashboard.kpi-card
            icon="clock-history"
            iconVariant="orange"
            label="Belum Disetor"
            valueVariant="orange"
            value="{{ $belumDisetorCount }} <span class='text-unit'> transaksi</span>"
            watermark="clock-history"
        >
            <x-slot:badge>
                @if($belumDisetorCount > 0)
                    <a href="{{ route('setoran.create') }}" class="btn-setor-now">Setor Sekarang</a>
                @endif
            </x-slot:badge>
            <div class="kpi-sub">
                @if($belumDisetorNominal > 0)
                    Rp {{ number_format($belumDisetorNominal, 0, ',', '.') }}
                @else
                    Semua sudah disetor
                @endif
            </div>
        </x-dashboard.kpi-card>
    </div>

    {{-- Belum Bayar --}}
    <div class="col-6 col-xl-3">
        <x-dashboard.kpi-card
            icon="person-x-fill"
            iconVariant="red"
            label="Belum Bayar Bulan Ini"
            valueVariant="red"
            value="{{ $jmlBelum }} <span class='text-unit'> siswa</span>"
            sub="dari {{ $totalSiswa }} total siswa"
            watermark="person-x-fill"
        >
            <x-slot:badge>
                <span class="kpi-badge kpi-badge--{{ $badgeLunasVariant }}">{{ $pctLunas }}% lunas</span>
            </x-slot:badge>
        </x-dashboard.kpi-card>
    </div>

</div>

{{-- Row 2: Grafik (8) + Aksi Cepat (4) ─────────────────── --}}
<div class="row g-3 mb-3">

    <div class="col-lg-8">
        <x-dashboard.card-panel
            title="Pemasukan {{ $jenjang }} — TA {{ $tahunPelajaran?->nama ?? '-' }}"
            icon="graph-up"
        >
            <x-slot:headerRight>
                <span class="grafik-total">
                    Total: <strong>Rp {{ number_format(array_sum($grafikData['data']), 0, ',', '.') }}</strong>
                </span>
            </x-slot:headerRight>
            <canvas id="grafikPemasukan" height="95"></canvas>
        </x-dashboard.card-panel>
    </div>

    <div class="col-lg-4">
        <x-dashboard.card-panel
            title="Aksi Cepat"
            icon="lightning-fill"
            iconClass="text-warning"
            class="h-100"
        >
            <div class="row g-2">
                <div class="col-6">
                    <x-dashboard.quick-btn href="{{ route('pembayaran.create') }}" icon="cash-coin"   iconClass="qb-icon--green"  label="Catat Bayar" />
                </div>
                <div class="col-6">
                    <x-dashboard.quick-btn href="{{ route('setoran.create') }}"   icon="wallet2"     iconClass="qb-icon--blue"   label="Buat Setoran" />
                </div>
                <div class="col-6">
                    <x-dashboard.quick-btn href="{{ route('pembayaran.index') }}" icon="list-check"  iconClass="qb-icon--navy"   label="Riwayat Bayar" />
                </div>
                <div class="col-6">
                    <x-dashboard.quick-btn href="{{ route('setoran.index') }}"    icon="archive"     iconClass="qb-icon--indigo" label="Riwayat Setor" />
                </div>
                <div class="col-6">
                    <x-dashboard.quick-btn href="{{ route('cetak.index') }}"      icon="printer"     iconClass="qb-icon--orange" label="Cetak Kartu" />
                </div>
                <div class="col-6">
                    <x-dashboard.quick-btn href="{{ route('siswa.index') }}"      icon="people"      iconClass="qb-icon--muted"  label="Data Siswa" />
                </div>
            </div>

            @if($belumDisetorCount > 0)
            <div class="reminder-box">
                <i class="bi bi-bell-fill reminder-box__icon"></i>
                <div>
                    <div class="reminder-box__title">{{ $belumDisetorCount }} transaksi belum disetor</div>
                    <div class="reminder-box__amount">
                        Total Rp {{ number_format($belumDisetorNominal, 0, ',', '.') }}
                    </div>
                    <a href="{{ route('setoran.create') }}" class="reminder-box__link">Setor sekarang →</a>
                </div>
            </div>
            @endif
        </x-dashboard.card-panel>
    </div>

</div>

{{-- Row 3: Belum Bayar (5) + Pembayaran Terbaru (4) + Setoran Terbaru (3) --}}
<div class="row g-3">

    <div class="col-lg-5">
        <x-dashboard.card-panel
            title="Belum Bayar {{ now()->isoFormat('MMM Y') }}"
            icon="person-x-fill"
            iconClass="text-danger"
            :scrollable="true"
        >
            <x-slot:titleExtra>
                <span class="kpi-badge kpi-badge--red ms-1">{{ $jmlBelum }}</span>
            </x-slot:titleExtra>
            <x-slot:headerRight>
                <x-dashboard.progress-bar :percent="$pctLunas" :colorVar="$progressColor" />
            </x-slot:headerRight>

            @forelse($siswaBelumBayar as $s)
                <x-dashboard.feed-siswa-item :siswa="$s" />
            @empty
                <x-dashboard.empty-state
                    icon="check-circle-fill"
                    iconVariant="green"
                    message="Semua sudah lunas!"
                    sub="Tidak ada tunggakan bulan ini."
                />
            @endforelse
        </x-dashboard.card-panel>
    </div>

    <div class="col-lg-4">
        <x-dashboard.card-panel
            title="Pembayaran Terbaru"
            icon="receipt"
            :seeAllHref="route('pembayaran.index')"
            :scrollable="true"
        >
            @forelse($pembayaranTerbaru as $p)
                <x-dashboard.feed-payment-item :payment="$p" />
            @empty
                <x-dashboard.empty-state icon="inbox" message="Belum ada pembayaran." />
            @endforelse
        </x-dashboard.card-panel>
    </div>

    <div class="col-lg-3">
        <x-dashboard.card-panel
            title="Setoran Terbaru"
            icon="wallet2"
            :seeAllHref="route('setoran.index')"
            :scrollable="true"
        >
            @forelse($setoranTerbaru as $s)
                <x-dashboard.feed-setoran-item :setoran="$s" />
            @empty
                <x-dashboard.empty-state icon="wallet2" message="Belum ada setoran." />
            @endforelse
        </x-dashboard.card-panel>
    </div>

</div>

@endif {{-- end role split --}}

@endsection

@push('scripts')
<script>
const ctx        = document.getElementById('grafikPemasukan').getContext('2d');
const grafikData = @json($grafikData);

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: grafikData.labels,
        datasets: [{
            label: 'Pemasukan',
            data: grafikData.data,
            backgroundColor: function(context) {
                const { ctx: c, chartArea } = context.chart;
                if (!chartArea) return 'rgba(37,99,235,.7)';
                const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                g.addColorStop(0, 'rgba(37,99,235,.88)');
                g.addColorStop(1, 'rgba(12,30,62,.5)');
                return g;
            },
            borderColor: 'transparent',
            borderRadius: 6,
            borderSkipped: false,
            hoverBackgroundColor: 'rgba(37,99,235,.98)',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0C1E3E',
                titleFont: { size: 11, family: 'DM Sans' },
                bodyFont: { size: 12, family: 'DM Sans', weight: '600' },
                padding: 10, cornerRadius: 8,
                callbacks: {
                    label: c => '  Rp ' + new Intl.NumberFormat('id-ID').format(c.raw)
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,.04)', drawBorder: false },
                border: { display: false },
                ticks: {
                    font: { size: 10, family: 'DM Sans' }, color: '#94A3B8',
                    maxTicksLimit: 5,
                    callback: val => {
                        if (val >= 1000000) return (val/1000000).toFixed(0) + 'jt';
                        if (val >= 1000)    return (val/1000).toFixed(0) + 'rb';
                        return val;
                    }
                }
            },
            x: {
                grid: { display: false }, border: { display: false },
                ticks: { font: { size: 10, family: 'DM Sans' }, color: '#94A3B8' }
            }
        }
    }
});
</script>
@endpush