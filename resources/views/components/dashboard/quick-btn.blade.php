{{-- resources/views/components/dashboard/quick-btn.blade.php --}}
{{--
    Props:
        href        string  URL tujuan
        icon        string  Bootstrap icon name (tanpa "bi-")
        iconClass   string  CSS class warna icon, e.g. "qb-icon--green"
        label       string  Label tombol
--}}
@props([
    'href',
    'icon',
    'iconClass' => '',
    'label',
])

<a href="{{ $href }}" class="quick-btn w-100">
    <i class="bi bi-{{ $icon }} {{ $iconClass }}"></i>
    {{ $label }}
</a>
