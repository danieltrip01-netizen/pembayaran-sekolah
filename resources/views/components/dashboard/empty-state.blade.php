{{-- resources/views/components/dashboard/empty-state.blade.php --}}
{{--
    Props:
        icon         string  Bootstrap icon name
        iconVariant  string  "green" | "muted"  (default: muted)
        message      string  Teks utama
        sub          string  Teks kecil di bawah (opsional)
--}}
@props([
    'icon',
    'iconVariant' => 'muted',
    'message',
    'sub' => null,
])

<div class="empty-state">
    <i class="bi bi-{{ $icon }} empty-state__icon empty-state__icon--{{ $iconVariant }}"></i>
    <div class="empty-state__title">{{ $message }}</div>
    @if($sub)
        <div class="empty-state__sub">{{ $sub }}</div>
    @endif
</div>
