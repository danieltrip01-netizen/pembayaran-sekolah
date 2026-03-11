{{-- resources/views/components/dashboard/card-panel.blade.php --}}
{{--
    Props:
        title        string   Teks judul card
        icon         string   Bootstrap icon name untuk judul (opsional)
        iconClass    string   CSS class warna icon (default: "text-primary")
        seeAllHref   string   URL tombol "Lihat Semua" (opsional)
        seeAllLabel  string   Label tombol (default: "Semua")
        scrollable   bool     Tambahkan max-height scroll pada body (default: false)
        noPad        bool     Hilangkan padding card-body (untuk tabel dll.)
    Named slots:
        $titleExtra  Konten tambahan setelah teks judul (badge count, dsb.)
        $headerRight Konten kanan header
        $slot        Konten utama card body
--}}
@props([
    'title',
    'icon'        => null,
    'iconClass'   => 'text-primary',
    'seeAllHref'  => null,
    'seeAllLabel' => 'Semua',
    'scrollable'  => false,
    'noPad'       => false,
])

<div {{ $attributes->merge(['class' => 'db-card panel']) }}>

    <div class="panel__header">
        <h6 class="panel__title">
            @if($icon)
                <i class="bi bi-{{ $icon }} panel__title-icon {{ $iconClass }}"></i>
            @endif
            {!! $title !!}
            {{ $titleExtra ?? '' }}
        </h6>

        {{ $headerRight ?? '' }}

        @if($seeAllHref)
            <a href="{{ $seeAllHref }}" class="panel__see-all">{{ $seeAllLabel }}</a>
        @endif
    </div>

    @if($noPad)
        {{ $slot }}
    @elseif($scrollable)
        <div class="panel__body panel__body--scroll">
            {{ $slot }}
        </div>
    @else
        <div class="panel__body">
            {{ $slot }}
        </div>
    @endif

</div>
