{{-- resources/views/components/dashboard/alert-warning.blade.php --}}
@props([
    'icon'       => 'exclamation-triangle-fill',
    'title',
    'message',
    'linkHref'   => null,
    'linkLabel'  => null,
])

<div class="alert-banner">
    <i class="bi bi-{{ $icon }} alert-banner__icon"></i>
    <div>
        <div class="alert-banner__title">{{ $title }}</div>
        <div class="alert-banner__msg">
            {{ $message }}
            @if($linkHref)
                <a href="{{ $linkHref }}" class="alert-banner__link">{{ $linkLabel }}</a>
            @endif
        </div>
    </div>
</div>
