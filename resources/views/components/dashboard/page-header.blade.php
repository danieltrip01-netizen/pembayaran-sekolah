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
            <span>{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
            <span class="dash-header__dot"></span>

            @if(!$isAdmin)
                <span class="badge-{{ $jenjang }}">{{ $jenjang }}</span>
            @else
                <span class="badge-admin">Admin Yayasan</span>
            @endif

            @if($tahunPelajaran)
                <span class="dash-header__dot"></span>
                <span class="badge-tahun">
                    <i class="bi bi-calendar-check me-1"></i>T.A. {{ $tahunPelajaran->nama }}
                </span>
            @endif
        </div>
    </div>
</div>
