{{-- resources/views/laporan/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Laporan Pembayaran')

@section('content')

@php
    // Cari label tahun pelajaran yang sedang dipilih
    $tahunDipilih = $tahunPelajaranList->firstWhere('id', $filter['tahun_pelajaran_id']);
    $isDefaultTahun = $tahunAktif && (string)$filter['tahun_pelajaran_id'] === (string)$tahunAktif->id;
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">Laporan Pembayaran</h4>
        <p class="text-muted small mb-0 d-flex align-items-center gap-2">
            {{-- Tahun pelajaran --}}
            @if($tahunDipilih)
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                    <i class="bi bi-calendar-range me-1"></i>{{ $tahunDipilih->nama }}
                </span>
                @if($isDefaultTahun)
                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.65rem">Tahun Aktif</span>
                @endif
            @endif
            {{-- Jenjang --}}
            @if(!empty($filter['jenjang']))
                <span class="badge badge-{{ strtolower($filter['jenjang']) }}">{{ $filter['jenjang'] }}</span>
            @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Semua Jenjang</span>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('laporan.pdf', array_filter(request()->all())) }}"
           class="btn btn-danger btn-sm" target="_blank">
            <i class="bi bi-file-pdf me-1"></i>Download PDF
        </a>
        <a href="{{ route('laporan.excel', array_filter(request()->all())) }}"
           class="btn btn-success btn-sm">
            <i class="bi bi-file-excel me-1"></i>Download Excel
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">

            {{-- ── Tahun Pelajaran ─────────────────────────────────────────── --}}
            <div class="col-md-2">
                <label class="form-label small fw-600 mb-1">Tahun Pelajaran</label>
                <select name="tahun_pelajaran_id" class="form-select form-select-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunPelajaranList as $tp)
                    <option value="{{ $tp->id }}"
                        {{ (string)($filter['tahun_pelajaran_id'] ?? '') === (string)$tp->id ? 'selected' : '' }}>
                        {{ $tp->nama }}{{ $tp->is_active ? ' ★' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- ── Jenjang ─────────────────────────────────────────────────── --}}
            @if(count($jenjangOptions) > 1)
            <div class="col-md-2">
                <label class="form-label small fw-600 mb-1">Jenjang</label>
                <select name="jenjang" class="form-select form-select-sm">
                    <option value="">Semua Jenjang</option>
                    @foreach($jenjangOptions as $j)
                    <option value="{{ $j }}" {{ ($filter['jenjang'] ?? '') === $j ? 'selected' : '' }}>
                        {{ $j }}
                    </option>
                    @endforeach
                </select>
            </div>
            @else
            <input type="hidden" name="jenjang" value="{{ $filter['jenjang'] }}">
            @endif

            {{-- ── Bulan Transaksi ─────────────────────────────────────────── --}}
            <div class="col-md-2">
                <label class="form-label small fw-600 mb-1">Bulan</label>
                <input type="month" name="bulan"
                       value="{{ $filter['bulan'] ?? '' }}"
                       class="form-control form-control-sm"
                       placeholder="Semua bulan">
            </div>

            {{-- ── Kelas ───────────────────────────────────────────────────── --}}
            <div class="col-md-2">
                <label class="form-label small fw-600 mb-1">Kelas</label>
                <select name="kelas" id="selectKelas" class="form-select form-select-sm">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasOptions as $k)
                    <option value="{{ $k }}" {{ ($filter['kelas'] ?? '') === $k ? 'selected' : '' }}>
                        Kelas {{ $k }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- ── Tombol ──────────────────────────────────────────────────── --}}
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-search me-1"></i>Tampilkan
                </button>
                <a href="{{ route('laporan.index') }}"
                   class="btn btn-outline-secondary btn-sm" title="Reset filter">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>


{{-- Rekap Cards --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Jumlah Transaksi</div>
                <div class="fw-bold fs-4 text-primary">{{ $rekap['jumlah_record'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Total SPP</div>
                <div class="fw-bold text-success small">
                    Rp {{ number_format($rekap['total_nominal'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Total Mamin</div>
                <div class="fw-bold text-info small">
                    Rp {{ number_format($rekap['total_mamin'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Grand Total</div>
                <div class="fw-bold" style="color:var(--primary)">
                    Rp {{ number_format($rekap['total_semua'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Rekap per Jenjang (hanya untuk admin yayasan & filter semua jenjang) --}}
@if(count($jenjangOptions) > 1 && empty($filter['jenjang']) && $pembayaran->isNotEmpty())
@php
    $perJenjang = $pembayaran->groupBy(fn($p) => $p->siswa->jenjang ?? '-');
    $jStyle = [
        'TK'  => ['color'=>'#db2777','bg'=>'#fce7f3','border'=>'#f9a8d4'],
        'SD'  => ['color'=>'#1d4ed8','bg'=>'#dbeafe','border'=>'#93c5fd'],
        'SMP' => ['color'=>'#059669','bg'=>'#d1fae5','border'=>'#6ee7b7'],
    ];
@endphp
<div class="row g-3 mb-3">
    @foreach(['TK','SD','SMP'] as $j)
    @if($perJenjang->has($j))
    @php $grp = $perJenjang[$j]; $js = $jStyle[$j]; @endphp
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-left:4px solid {{ $js['color'] }} !important">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge rounded-pill px-2"
                          style="background:{{ $js['bg'] }};color:{{ $js['color'] }};border:1px solid {{ $js['border'] }}">
                        {{ $j }}
                    </span>
                    <small class="text-muted">{{ $grp->pluck('siswa_id')->unique()->count() }} siswa</small>
                </div>
                <div class="fw-bold" style="color:{{ $js['color'] }}">
                    Rp {{ number_format($grp->sum('total_bayar'), 0, ',', '.') }}
                </div>
                <div class="text-muted" style="font-size:.72rem">{{ $grp->count() }} transaksi</div>
            </div>
        </div>
    </div>
    @endif
    @endforeach
</div>
@endif

{{-- Rekap Per Kelas --}}
@if($perKelas->isNotEmpty())
<div class="card mb-3">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-table me-2" style="color:var(--primary)"></i>Rekap Per Kelas
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Kelas</th>
                    @if(count($jenjangOptions) > 1 && empty($filter['jenjang']))
                    <th>Jenjang</th>
                    @endif
                    <th>Jumlah Siswa</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($perKelas as $kelas => $data)
                <tr>
                    <td><strong>Kelas {{ $kelas !== '-' ? $kelas : '(tanpa kelas)' }}</strong></td>
                    @if(count($jenjangOptions) > 1 && empty($filter['jenjang']))
                    <td>
                        @php
                            $jKelas = $pembayaran->filter(fn($p) => ($p->siswa->kelas ?? '-') === $kelas)
                                        ->groupBy(fn($p) => $p->siswa->jenjang ?? '-')
                                        ->keys()->first() ?? '-';
                            $js2 = $jStyle[$jKelas] ?? ['color'=>'#64748b','bg'=>'#f1f5f9','border'=>'#e2e8f0'];
                        @endphp
                        <span class="badge rounded-pill px-2"
                              style="background:{{ $js2['bg'] }};color:{{ $js2['color'] }};border:1px solid {{ $js2['border'] }}">
                            {{ $jKelas }}
                        </span>
                    </td>
                    @endif
                    <td>{{ $data['jumlah_siswa'] }} siswa</td>
                    <td class="text-end fw-600 text-success">
                        Rp {{ number_format($data['total'], 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc">
                    <td colspan="{{ (count($jenjangOptions) > 1 && empty($filter['jenjang'])) ? 3 : 2 }}"
                        class="text-end fw-bold text-muted small">TOTAL:</td>
                    <td class="text-end fw-bold text-primary">
                        Rp {{ number_format($rekap['total_semua'], 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

{{-- Detail Transaksi --}}
<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-list-ul me-2" style="color:var(--primary)"></i>
            Detail Transaksi
            @if($pembayaran->isNotEmpty())
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1">
                {{ $pembayaran->count() }}
            </span>
            @endif
        </h6>
        @if($tahunDipilih)
        <small class="text-muted">
            <i class="bi bi-calendar-range me-1"></i>{{ $tahunDipilih->nama }}
        </small>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table mb-0" style="font-size:.82rem">
            <thead>
                <tr>
                    <th style="width:32px">No</th>
                    <th>Tanggal</th>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Jenjang</th>
                    <th>Bulan</th>
                    <th class="text-end">SPP</th>
                    <th class="text-end">Donatur</th>
                    <th class="text-end">Mamin</th>
                    <th class="text-end">Total</th>
                    <th>Petugas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembayaran as $idx => $p)
                @php
                    $jn = $p->siswa->jenjang ?? 'sd';
                    $js3 = $jStyle[$jn] ?? ['color'=>'#64748b','bg'=>'#f1f5f9','border'=>'#e2e8f0'];
                @endphp
                <tr>
                    <td class="text-muted">{{ $idx + 1 }}</td>
                    <td class="text-muted">{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
                    <td class="fw-600">
                        <a href="{{ route('pembayaran.show', $p) }}"
                           class="text-decoration-none" style="color:var(--primary)">
                            {{ $p->siswa->nama ?? '-' }}
                        </a>
                    </td>
                    {{-- FIX: filter siswaKelas berdasarkan tahun_pelajaran_id yang aktif/dipilih --}}
                    <td>{{ $p->siswa?->siswaKelas->firstWhere('tahun_pelajaran_id', $filter['tahun_pelajaran_id'])?->kelas?->nama ?? '-' }}</td>
                    <td>
                        <span class="badge rounded-pill px-2"
                              style="background:{{ $js3['bg'] }};color:{{ $js3['color'] }};border:1px solid {{ $js3['border'] }};font-size:.65rem">
                            {{ $jn }}
                        </span>
                    </td>
                    <td>{{ $p->bulan_label }}</td>
                    <td class="text-end">{{ number_format($p->nominal_per_bulan * $p->jumlah_bulan, 0, ',', '.') }}</td>
                    <td class="text-end text-danger">
                        @if($p->nominal_donator > 0)
                            {{ number_format($p->nominal_donator, 0, ',', '.') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end text-info">
                        @if($p->nominal_mamin > 0)
                            {{ number_format($p->nominal_mamin, 0, ',', '.') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end fw-600 text-success">
                        {{ number_format($p->total_bayar, 0, ',', '.') }}
                    </td>
                    <td class="text-muted">{{ $p->user->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-30"></i>
                        <div class="fw-600 mb-1">Tidak ada data</div>
                        <div class="small">
                            @if(!empty($filterAktif))
                                Coba ubah atau reset filter di atas.
                            @elseif($tahunAktif)
                                Belum ada pembayaran untuk tahun pelajaran
                                <strong>{{ $tahunAktif->nama }}</strong>.
                            @else
                                Belum ada pembayaran yang dicatat.
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>

            @if($pembayaran->isNotEmpty())
            <tfoot>
                <tr class="fw-bold" style="background:#f0f4f8">
                    <td colspan="6" class="text-end text-muted small">TOTAL:</td>
                    <td class="text-end">
                        {{ number_format($rekap['total_nominal'], 0, ',', '.') }}
                    </td>
                    <td class="text-end text-danger">
                        {{ number_format($rekap['total_donator'], 0, ',', '.') }}
                    </td>
                    <td class="text-end text-info">
                        {{ number_format($rekap['total_mamin'], 0, ',', '.') }}
                    </td>
                    <td class="text-end text-success">
                        {{ number_format($rekap['total_semua'], 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Hanya aktif untuk admin yayasan (jika select#selectJenjang ada di DOM)
(function () {
    const kelasByJenjang = {
        'TK' : ['A', 'B'],
        'SD' : ['I', 'II', 'III', 'IV', 'V', 'VI'],
        'SMP': ['VII', 'VIII', 'IX'],
    };
    const allKelas = ['A','B','I','II','III','IV','V','VI','VII','VIII','IX'];

    const selJenjang = document.querySelector('select[name="jenjang"]');
    const selKelas   = document.getElementById('selectKelas');

    if (!selJenjang || !selKelas) return; // admin jenjang pakai hidden input, skip

    function updateKelasOptions(jenjang, selectedKelas) {
        const options = jenjang ? (kelasByJenjang[jenjang] || []) : allKelas;
        const current = selectedKelas ?? selKelas.value;

        // Kosongkan lalu isi ulang
        selKelas.innerHTML = '<option value="">Semua Kelas</option>';
        options.forEach(function (k) {
            const opt = document.createElement('option');
            opt.value       = k;
            opt.textContent = 'Kelas ' + k;
            if (k === current) opt.selected = true;
            selKelas.appendChild(opt);
        });

        // Jika nilai sebelumnya tidak ada di jenjang baru, reset ke "Semua Kelas"
        if (current && !options.includes(current)) {
            selKelas.value = '';
        }
    }

    // Inisialisasi saat halaman load (sesuaikan dengan nilai filter saat ini)
    updateKelasOptions(selJenjang.value, '{{ $filter['kelas'] ?? '' }}');

    // Update setiap kali jenjang diganti
    selJenjang.addEventListener('change', function () {
        updateKelasOptions(this.value, null);
    });
}());
</script>
@endpush