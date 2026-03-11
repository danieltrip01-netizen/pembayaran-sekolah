{{-- resources/views/components/dashboard/progress-bar.blade.php --}}
{{--
    Props:
        percent   int     Persentase lebar (0–100)
        colorVar  string  CSS variable atau nilai warna (default: var(--blue))
--}}
@props([
    'percent'  => 0,
    'colorVar' => 'var(--blue)',
])

<div class="progress-track">
    <div class="progress-fill" style="width:{{ $percent }}%;background:{{ $colorVar }}"></div>
</div>
