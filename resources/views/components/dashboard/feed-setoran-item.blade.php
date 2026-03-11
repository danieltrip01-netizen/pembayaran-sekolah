{{-- resources/views/components/dashboard/feed-setoran-item.blade.php --}}
@props(['setoran'])

<a href="{{ route('setoran.show', $setoran) }}" class="feed-item">
    <div class="feed-dot feed-dot--blue"></div>
    <div class="flex-grow-1 overflow-hidden">
        <div class="feed-name text-truncate">{{ $setoran->kode_setoran }}</div>
        <div class="feed-meta">{{ $setoran->tanggal_setoran->format('d/m/Y') }}</div>
        <div class="feed-amount" style="font-size:.78rem">
            Rp {{ number_format($setoran->total_keseluruhan, 0, ',', '.') }}
        </div>
    </div>
</a>
