{{-- resources/views/components/dashboard/feed-payment-item.blade.php --}}
@props(['payment'])

<div class="feed-item">

    <div class="feed-dot feed-dot--green"></div>

    <div class="flex-grow-1 overflow-hidden">
        <div class="feed-name text-truncate">{{ $payment->siswa->nama ?? '—' }}</div>
        <div class="feed-meta">
            {{ $payment->bulan_label }} &mdash; {{ $payment->tanggal_bayar->format('d/m/Y') }}
        </div>
    </div>

    <div class="text-end flex-shrink-0">
        <div class="feed-amount">
            Rp {{ number_format($payment->total_bayar, 0, ',', '.') }}
        </div>
        @if($payment->setoran_id)
            <div class="feed-amount-label feed-amount-label--done">✓ Disetor</div>
        @else
            <div class="feed-amount-label feed-amount-label--pending">Belum disetor</div>
        @endif
    </div>

</div>
