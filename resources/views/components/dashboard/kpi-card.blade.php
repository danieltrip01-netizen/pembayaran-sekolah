{{-- resources/views/components/dashboard/kpi-card.blade.php --}}
{{--
    Props:
        icon        string  Bootstrap icon name (tanpa "bi-"), e.g. "people-fill"
        iconVariant string  "blue" | "green" | "yellow" | "red" | "orange"
        label       string  Label KPI (uppercase kecil)
        value       string  Nilai utama (boleh HTML)
        valueSize   string  "lg" | "md"  (default: lg)
        valueVariant string "navy" | "green" | "yellow" | "red" | "orange"
        sub         string  Teks kecil di bawah nilai (opsional)
        watermark   string  Bootstrap icon untuk watermark (opsional)
    Named slots:
        $badge      Konten badge kanan atas
        $slot       Konten tambahan di bawah nilai
--}}
@props([
    'icon',
    'iconVariant'  => 'blue',
    'label',
    'value',
    'valueSize'    => 'lg',
    'valueVariant' => 'navy',
    'sub'          => null,
    'watermark'    => null,
])

<div class="db-card kpi-card">
    <div class="kpi-card__body">

        <div class="kpi-card__top">
            <div class="kpi-icon kpi-icon--{{ $iconVariant }}">
                <i class="bi bi-{{ $icon }}"></i>
            </div>
            {{ $badge ?? '' }}
        </div>

        <div class="kpi-label">{{ $label }}</div>

        <div class="kpi-value kpi-value--{{ $valueVariant }} kpi-value--{{ $valueSize }}">
            {!! $value !!}
        </div>

        @if($sub)
            <div class="kpi-sub">{{ $sub }}</div>
        @endif

        {{ $slot }}

    </div>

    @if($watermark)
        <i class="bi bi-{{ $watermark }} kpi-watermark"></i>
    @endif
</div>
