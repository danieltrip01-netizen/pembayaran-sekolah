{{-- resources/views/components/dashboard/feed-siswa-item.blade.php --}}
@props([
    'siswa',
    'showJenjang' => false,
])

<div class="feed-item">

    <div class="feed-avatar">
        {{ strtoupper(substr($siswa->nama, 0, 1)) }}
    </div>

    <div class="flex-grow-1 overflow-hidden">
        <div class="feed-name text-truncate">{{ $siswa->nama }}</div>
        <div class="feed-meta">
            @if($showJenjang)
                <span class="badge-{{ $siswa->jenjang }}">{{ $siswa->jenjang }}</span>
            @endif
            Kelas {{ $siswa->kelasAktif?->kelas?->nama ?? '—' }}
        </div>
    </div>

    <a href="{{ route('pembayaran.create', ['siswa_id' => $siswa->id]) }}" class="btn-bayar">
        Bayar
    </a>

</div>
