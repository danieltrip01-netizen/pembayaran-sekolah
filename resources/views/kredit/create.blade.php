{{-- resources/views/kredit/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Kredit — ' . $siswa->nama)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.index') }}">Data Siswa</a></li>
    <li class="breadcrumb-item"><a href="{{ route('siswa.show', $siswa) }}">{{ $siswa->nama }}</a></li>
    <li class="breadcrumb-item active">Kredit</li>
@endsection

@section('content')

@php
$jStyle = match($siswa->jenjang) {
    'TK'  => ['color'=>'#db2777','bg'=>'#fce7f3','border'=>'#f9a8d4'],
    'SD'  => ['color'=>'#1d4ed8','bg'=>'#dbeafe','border'=>'#93c5fd'],
    'SMP' => ['color'=>'#059669','bg'=>'#d1fae5','border'=>'#6ee7b7'],
    default => ['color'=>'#64748b','bg'=>'#f1f5f9','border'=>'#e2e8f0'],
};
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color:var(--primary)">Saldo Kredit Siswa</h4>
        <p class="text-muted small mb-0">
            <span class="badge rounded-pill me-1"
                  style="background:{{ $jStyle['bg'] }};color:{{ $jStyle['color'] }};border:1px solid {{ $jStyle['border'] }}">
                {{ $siswa->jenjang }}
            </span>
            {{ $siswa->nama }} · {{ $siswa->kelasAktif?->kelas?->nama ?? '-' }}
        </p>
    </div>
    <a href="{{ route('siswa.show', $siswa) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Siswa
    </a>
</div>

<div class="row g-4">

    {{-- ═══ Kiri: Saldo & Form Tambah ═══ --}}
    <div class="col-md-4">

        {{-- Saldo saat ini --}}
        <div class="card mb-3" style="border-left:4px solid {{ $siswa->saldo_kredit > 0 ? '#059669' : '#94a3b8' }} !important">
            <div class="card-body text-center py-4">
                <div class="text-muted small mb-1">Saldo Kredit Saat Ini</div>
                <div class="fw-bold mb-1"
                     style="font-size:2rem;color:{{ $siswa->saldo_kredit > 0 ? '#059669' : '#94a3b8' }}">
                    Rp {{ number_format($siswa->saldo_kredit, 0, ',', '.') }}
                </div>
                @if($siswa->saldo_kredit > 0)
                <div class="text-success small">
                    <i class="bi bi-info-circle me-1"></i>
                    Akan dipotong otomatis saat input pembayaran berikutnya
                </div>
                @else
                <div class="text-muted small">Tidak ada saldo kredit</div>
                @endif
            </div>
        </div>        
    </div>

    {{-- ═══ Kanan: Riwayat Log ═══ --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold" style="color:var(--primary)">
                    <i class="bi bi-clock-history me-2"></i>Riwayat Kredit
                </h6>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                    {{ $log->total() }} entri
                </span>
            </div>

            @if($log->isEmpty())
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-30"></i>
                Belum ada riwayat kredit untuk siswa ini.
            </div>
            @else
            <div class="table-responsive">
                <table class="table mb-0" style="font-size:.85rem">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Tipe</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-end">Saldo Sesudah</th>
                            <th>Keterangan</th>
                            <th>Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($log as $l)
                        <tr>
                            <td class="text-muted">{{ $l->created_at->isoFormat('D MMM Y HH:mm') }}</td>
                            <td>
                                @if($l->tipe === 'tambah')
                                <span class="badge" style="background:#d1fae5;color:#059669;border:1px solid #6ee7b7">
                                    <i class="bi bi-arrow-up-circle me-1"></i>Tambah
                                </span>
                                @else
                                <span class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fcd34d">
                                    <i class="bi bi-arrow-down-circle me-1"></i>Pakai
                                </span>
                                @endif
                            </td>
                            <td class="text-end fw-bold"
                                style="color:{{ $l->tipe==='tambah'?'#059669':'#b45309' }}">
                                {{ $l->tipe==='tambah' ? '+' : '-' }}Rp {{ number_format($l->jumlah,0,',','.') }}
                            </td>
                            <td class="text-end" style="color:var(--primary)">
                                Rp {{ number_format($l->saldo_sesudah,0,',','.') }}
                            </td>
                            <td>
                                <span class="text-muted">{{ $l->keterangan }}</span>
                                @if($l->pembayaran)
                                <br><a href="{{ route('pembayaran.show', $l->pembayaran) }}"
                                       class="small text-decoration-none" style="color:var(--primary)">
                                    <code>{{ $l->pembayaran->kode_bayar }}</code>
                                </a>
                                @endif
                            </td>
                            <td class="text-muted">{{ $l->user->name ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($log->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                <div class="text-muted small">
                    {{ $log->firstItem() }}–{{ $log->lastItem() }} dari {{ $log->total() }}
                </div>
                {{ $log->links() }}
            </div>
            @endif
            @endif

        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
function hitung() {
    const selisih = parseInt(document.getElementById('calcSelisih').value) || 0;
    const bulan   = parseInt(document.getElementById('calcBulan').value) || 0;
    const total   = selisih * bulan;

    if (total > 0) {
        document.getElementById('calcAngka').textContent =
            total.toLocaleString('id-ID');
        document.getElementById('calcResult').style.display = '';

        // Isi langsung ke form
        document.getElementById('inputJumlah').value = total;
    }
}
// Enter trigger
['calcSelisih','calcBulan'].forEach(id => {
    document.getElementById(id)?.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); hitung(); }
    });
});
</script>
@endpush