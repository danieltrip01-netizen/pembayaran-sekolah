{{-- resources/views/components/dashboard/page-header.blade.php --}}
@props([
    'isAdmin',
    'jenjang'        => null,
    'tahunPelajaran' => null,
])

<div class="dash-header">
    <div>
        <div class="dash-header__greeting">
            Halo, {{ auth()->user()->nama_lengkap ?? auth()->user()->name }} 👋
        </div>

        <div class="dash-header__meta">
            @if($tahunPelajaran)
                <span class="badge-tahun">
                    <i class="bi bi-calendar-check me-1"></i>T.A. {{ $tahunPelajaran->nama }}
                </span>
            @endif
        </div>
    </div>
</div>
