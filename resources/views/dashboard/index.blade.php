{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color: var(--primary)">Dashboard</h4>
        <p class="text-muted small mb-0">Selamat datang, {{ auth()->user()->nama_lengkap ?? auth()->user()->name }}</p>
    </div>
    <div class="text-muted small">
        <i class="bi bi-calendar3 me-1"></i>{{ now()->isoFormat('D MMMM Y') }}
    </div>
</div>



<!-- STAT CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small mb-1">Total Siswa Aktif</div>
                    <div class="fw-bold fs-3" style="color: var(--primary)">{{ number_format($totalSiswa) }}</div>
                    <div class="text-muted" style="font-size:.75rem">siswa terdaftar</div>
                </div>
                <div class="icon-box" style="background: #dbeafe; color: var(--primary)">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small mb-1">Total Pemasukan</div>
                    <div class="fw-bold fs-5" style="color: #16a34a">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</div>
                    <div class="text-muted" style="font-size:.75rem">semua waktu</div>
                </div>
                <div class="icon-box" style="background: #dcfce7; color: #16a34a">
                    <i class="bi bi-bank2"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small mb-1">Pemasukan Bulan Ini</div>
                    <div class="fw-bold fs-5" style="color: var(--gold)">Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}</div>
                    <div class="text-muted" style="font-size:.75rem">{{ now()->isoFormat('MMMM Y') }}</div>
                </div>
                <div class="icon-box" style="background: #fef3c7; color: var(--gold)">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small mb-1">Belum Bayar Bulan Ini</div>
                    <div class="fw-bold fs-3 text-danger">{{ $siswaBelumBayar->count() }}</div>
                    <div class="text-muted" style="font-size:.75rem">siswa</div>
                </div>
                <div class="icon-box" style="background: #fee2e2; color: #dc2626">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- GRAFIK & SISWA BELUM BAYAR -->
<div class="row g-3">
    <!-- Grafik -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold" style="color: var(--primary)">
                    <i class="bi bi-graph-up me-2"></i>Grafik Pemasukan 12 Bulan Terakhir
                </h6>
            </div>
            <div class="card-body">
                <canvas id="grafikPemasukan" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Siswa Belum Bayar -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>Belum Bayar Bulan Ini
                </h6>
            </div>
            <div class="card-body p-0" style="max-height: 350px; overflow-y: auto">
                @forelse($siswaBelumBayar as $siswa)
                <div class="d-flex align-items-center gap-2 p-3 border-bottom hover-bg">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                         style="width:35px;height:35px;font-size:.8rem;background: var(--primary)">
                        {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-600 text-truncate" style="font-size:.85rem">{{ $siswa->nama }}</div>
                        <div class="text-muted" style="font-size:.75rem">
                            {{ $siswa->jenjang }} - Kelas {{ $siswa->kelas }}
                        </div>
                    </div>
                    <a href="{{ route('pembayaran.create', ['siswa_id' => $siswa->id]) }}"
                       class="btn btn-sm btn-outline-primary rounded-pill" style="font-size:.7rem">
                        Bayar
                    </a>
                </div>
                @empty
                <div class="text-center p-4 text-success">
                    <i class="bi bi-check-circle-fill fs-2 mb-2 d-block"></i>
                    <span class="small">Semua siswa sudah membayar!</span>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Setoran Terbaru -->
@if($setoranTerbaru->isNotEmpty())
<div class="card mt-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold" style="color: var(--primary)">
            <i class="bi bi-clock-history me-2"></i>Setoran Terbaru
        </h6>
        <a href="{{ route('setoran.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Jenjang</th>
                    <th>Total</th>
                    <th>Petugas</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($setoranTerbaru as $setoran)
                <tr>
                    <td class="fw-600">{{ $setoran->kode_setoran }}</td>
                    <td>{{ $setoran->tanggal_setoran->format('d/m/Y') }}</td>
                    <td><span class="badge badge-{{ strtolower($setoran->jenjang) }}">{{ $setoran->jenjang }}</span></td>
                    <td class="fw-600 text-success">Rp {{ number_format($setoran->total_keseluruhan, 0, ',', '.') }}</td>
                    <td>{{ $setoran->user->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('setoran.show', $setoran) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('grafikPemasukan').getContext('2d');
const grafikData = @json($grafikData);

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: grafikData.labels,
        datasets: [{
            label: 'Pemasukan (Rp)',
            data: grafikData.data,
            backgroundColor: 'rgba(27, 75, 138, 0.8)',
            borderColor: 'rgba(27, 75, 138, 1)',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: val => 'Rp ' + new Intl.NumberFormat('id-ID').format(val)
                }
            }
        }
    }
});
</script>
@endpush