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

{{-- KPI Row ─────────────────────────────────────────────── --}}
<div class="row g-3 mb-3">

    {{-- Siswa Jenjang --}}
    <div class="col-6 col-xl-3">
        <x-dashboard.kpi-card
            class="h-100"
            icon="people-fill"
            label="Siswa Aktif"
            value="{{ number_format($totalSiswa) }}"
            watermark="people-fill"
        >
            <div class="mt-2">
                <x-dashboard.progress-bar :percent="$pctLunas" colorVar="var(--green)" />
                <div class="kpi-sub">{{ $totalSiswa - $jmlBelum }} dari {{ $totalSiswa }} lunas bulan ini</div>
            </div>
        </x-dashboard.kpi-card>
    </div>

    {{-- Pemasukan Bulan Ini --}}
    <div class="col-6 col-xl-3">
        <x-dashboard.kpi-card
            class="h-100"
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
            class="h-100"
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
            class="h-100"
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
            title="Pemasukan — T.A. {{ $tahunPelajaran?->nama ?? '-' }}"
            icon="graph-up"
            class="h-100"
        >
            <x-slot:headerRight>
                <span class="grafik-total">
                    Total: <strong>Rp {{ number_format(array_sum($grafikData['data']), 0, ',', '.') }}</strong>
                </span>
            </x-slot:headerRight>
            <canvas id="grafikPemasukan" style="width:100%;flex:1;min-height:0"></canvas>
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

           
        </x-dashboard.card-panel>
    </div>

</div>

{{-- Row 3: Lunas Semester (5) + Pembayaran Terbaru (4) + Setoran Terbaru (3) --}}
<div class="row g-3">

    <div class="col-lg-5 d-flex flex-column">
        <x-dashboard.card-panel
            title="Status Tagihan"
            icon="person-check-fill"
            iconClass="text-success"
            class="h-100"
            :scrollable="true"
        >
            <x-slot:headerRight>
                <div class="d-flex align-items-center gap-2">
                    {{-- Filter Semester --}}
                    <div class="btn-group btn-group-sm" role="group" id="filterSemesterGroup">
                        <button type="button"
                            class="btn btn-outline-success filter-smt-btn active"
                            data-filter="lunas"
                            title="Siswa yang sudah lunas penuh 1 tahun (Jul–Jun)">
                            ✓ Lunas
                        </button>
                        <button type="button"
                            class="btn btn-outline-danger filter-smt-btn"
                            data-filter="smt1"
                            title="Siswa yang belum lunas semester 1 (Jul–Des)">
                            Smt 1
                        </button>
                        <button type="button"
                            class="btn btn-outline-danger filter-smt-btn"
                            data-filter="smt2"
                            title="Siswa yang belum lunas semester 2 (Jan–Jun)">
                            Smt 2
                        </button>
                    </div>
                    {{-- Tombol Salin --}}
                    <button type="button"
                        class="btn btn-sm btn-outline-secondary"
                        id="btnSalinNama"
                        title="Salin daftar nama siswa">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </x-slot:headerRight>

            {{-- List: Lunas Semester --}}
            <div id="listLunas" class="semester-list">
                @forelse($siswaLunasSemester as $s)
                    <x-dashboard.feed-siswa-item :siswa="$s" />
                @empty
                    <x-dashboard.empty-state
                        icon="hourglass-split"
                        iconVariant="warning"
                        message="Belum ada yang lunas."
                        sub="Belum ada siswa yang menyelesaikan semua 12 bulan."
                    />
                @endforelse
            </div>

            {{-- List: Belum Lunas Semester 1 --}}
            <div id="listBelumSmt1" class="semester-list d-none">
                @forelse($siswaBelumLunasSmt1 as $s)
                    <x-dashboard.feed-siswa-item :siswa="$s" />
                @empty
                    <x-dashboard.empty-state
                        icon="check-circle-fill"
                        iconVariant="green"
                        message="Semua sudah lunas semester 1!"
                        sub="Tidak ada tunggakan di semester 1."
                    />
                @endforelse
            </div>

            {{-- List: Belum Lunas Semester 2 --}}
            <div id="listBelumSmt2" class="semester-list d-none">
                @forelse($siswaBelumLunasSmt2 as $s)
                    <x-dashboard.feed-siswa-item :siswa="$s" />
                @empty
                    <x-dashboard.empty-state
                        icon="check-circle-fill"
                        iconVariant="green"
                        message="Semua sudah lunas semester 2!"
                        sub="Tidak ada tunggakan di semester 2."
                    />
                @endforelse
            </div>

        </x-dashboard.card-panel>
    </div>

    <div class="col-lg-4 d-flex flex-column">
        <x-dashboard.card-panel
            title="Pembayaran Terbaru"
            icon="receipt"
            class="h-100"
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

    <div class="col-lg-3 d-flex flex-column">
        <x-dashboard.card-panel
            title="Setoran Terbaru"
            icon="wallet2"
            class="h-100"
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
        maintainAspectRatio: false,
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
// ── Filter Semester & Salin Nama ─────────────────────────────────────────
(function () {
    const countMap = {
        lunas : {{ $jmlLunasSemester }},
        smt1  : {{ $siswaBelumLunasSmt1->count() }},
        smt2  : {{ $siswaBelumLunasSmt2->count() }},
    };

    const listMap = {
        lunas : document.getElementById('listLunas'),
        smt1  : document.getElementById('listBelumSmt1'),
        smt2  : document.getElementById('listBelumSmt2'),
    };

    const badge  = document.getElementById('badgeJmlSiswa');
    const btnCopy = document.getElementById('btnSalinNama');
    let activeFilter = 'lunas';

    // Warna badge sesuai filter
    const badgeClass = { lunas: 'kpi-badge--green', smt1: 'kpi-badge--red', smt2: 'kpi-badge--red' };

    document.getElementById('filterSemesterGroup')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.filter-smt-btn');
        if (!btn) return;

        activeFilter = btn.dataset.filter;

        // Toggle active button style
        this.querySelectorAll('.filter-smt-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Toggle list visibility
        Object.entries(listMap).forEach(([key, el]) => {
            el?.classList.toggle('d-none', key !== activeFilter);
        });

        // Update badge warna & angka
        if (badge) {
            badge.textContent = countMap[activeFilter] ?? 0;
            badge.className   = 'kpi-badge ms-1 ' + (badgeClass[activeFilter] ?? 'kpi-badge--green');
        }
    });

    // Salin daftar nama siswa yang sedang tampil — dikelompokkan per kelas
    btnCopy?.addEventListener('click', function () {
        const activeList = listMap[activeFilter];
        if (!activeList) return;

        const items = [...activeList.querySelectorAll('.siswa-item-wrap')];

        if (!items.length) {
            btnCopy.innerHTML = '<i class="bi bi-clipboard-x"></i>';
            setTimeout(() => btnCopy.innerHTML = '<i class="bi bi-clipboard"></i>', 1500);
            return;
        }

        // Kelompokkan per kelas
        const groups = new Map();
        items.forEach(el => {
            const kelas = el.dataset.kelas || 'Tanpa Kelas';
            const nama  = el.dataset.nama  || '-';
            if (!groups.has(kelas)) groups.set(kelas, []);
            groups.get(kelas).push(nama);
        });

        // Urutkan kelas dari terendah (natural sort: "Kelas 1A" < "Kelas 2B" < "Kelas 10A")
        const sortedGroups = [...groups.entries()].sort(([a], [b]) =>
            a.localeCompare(b, 'id', { numeric: true, sensitivity: 'base' })
        );

        const labelMap = { lunas: 'Lunas Penuh (1 Tahun)', smt1: 'Belum Lunas Smt 1', smt2: 'Belum Lunas Smt 2' };
        const lines    = [`=== ${labelMap[activeFilter]} ===`];

        sortedGroups.forEach(([kelas, namaList]) => {
            lines.push(`\n[ ${kelas} ]`);
            namaList.forEach((nama, i) => lines.push(`${i + 1}. ${nama}`));
        });

        navigator.clipboard.writeText(lines.join('\n')).then(() => {
            btnCopy.innerHTML = '<i class="bi bi-clipboard-check text-success"></i>';
            setTimeout(() => btnCopy.innerHTML = '<i class="bi bi-clipboard"></i>', 2000);
        }).catch(() => {
            btnCopy.innerHTML = '<i class="bi bi-clipboard-x text-danger"></i>';
            setTimeout(() => btnCopy.innerHTML = '<i class="bi bi-clipboard"></i>', 2000);
        });
    });
})();
</script>
@endpush