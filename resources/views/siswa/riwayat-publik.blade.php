{{-- resources/views/siswa/riwayat-pembayaran.blade.php --}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Paksa WA pakai gambar kosong sebagai preview --}}
    <meta property="og:title"       content="Riwayat Pembayaran SPP">
    <meta property="og:description" content="{{ $namaSekolah }}">
    <meta property="og:image"       content="{{ asset('img/blank.png') }}">
    <meta property="og:image:width"  content="1">
    <meta property="og:image:height" content="1">
    <meta property="og:type"   
    
    <title></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy:         #0c1e3e;
            --navy-mid:     #1a3460;
            --blue:         #2563eb;
            --blue-light:   #dbeafe;
            --green:        #16a34a;
            --green-light:  #dcfce7;
            --green-ring:   #86efac;
            --red:          #dc2626;
            --red-light:    #fee2e2;
            --amber:        #d97706;
            --amber-light:  #fef3c7;
            --surface:      #f1f5f9;
            --border:       #e2e8f0;
            --card:         #ffffff;
            --ink:          #0f172a;
            --ink-soft:     #334155;
            --ink-muted:    #64748b;
            --ink-faint:    #94a3b8;
            --r-sm:         8px;
            --r:            12px;
            --r-lg:         18px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--surface);
            color: var(--ink);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── HERO ─────────────────────────────────────────────── */
        .hero {
            background: linear-gradient(150deg, var(--navy) 0%, var(--navy-mid) 55%, #1d4480 100%);
            padding: 28px 20px 76px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute; top: -50px; right: -50px;
            width: 200px; height: 200px; border-radius: 50%;
            background: rgba(255,255,255,.04);
        }
        .hero::after {
            content: '';
            position: absolute; bottom: -50px; left: -30px;
            width: 180px; height: 180px; border-radius: 50%;
            background: rgba(37,99,235,.12);
        }

        .hero-header {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 22px; position: relative; z-index: 1;
        }

        .hero-logo {
            width: 42px; height: 42px;  }

        .hero-logo-ph {
            width: 42px; height: 42px; border-radius: 10px;
            background: rgba(255,255,255,.12);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .school-name {
            flex: 1; font-size: 13px; font-weight: 700;
            color: #fff; line-height: 1.35;
        }

        .school-sub {
            font-size: 11px; color: rgba(255,255,255,.5);
            margin-top: 1px;
        }

        .ta-badge {
            font-size: 10px; font-weight: 600;
            color: rgba(255,255,255,.75);
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 20px; padding: 3px 10px;
            white-space: nowrap;
        }

        .hero-body { position: relative; z-index: 1; }

        .hero-label {
            font-size: 10px; font-weight: 600;
            color: rgba(255,255,255,.45);
            text-transform: uppercase; letter-spacing: .1em;
            margin-bottom: 4px;
        }

        .hero-name {
            font-size: 23px; font-weight: 800;
            color: #fff; line-height: 1.2; margin-bottom: 10px;
        }

        .hero-chips { display: flex; gap: 7px; flex-wrap: wrap; }

        .chip {
            font-size: 11.5px; font-weight: 600;
            color: rgba(255,255,255,.85);
            background: rgba(255,255,255,.12);
            border-radius: 20px; padding: 4px 11px;
            display: flex; align-items: center; gap: 4px;
        }

        /* ─── STATS CARD ────────────────────────────────────────── */
        .stats-wrap {
            margin: -44px 16px 0;
            position: relative; z-index: 10;
        }

        .stats-card {
            background: var(--card);
            border-radius: var(--r-lg);
            box-shadow: 0 8px 32px rgba(0,0,0,.13);
            display: grid; grid-template-columns: 1fr 1fr 1fr;
        }

        .stat {
            text-align: center; padding: 16px 8px;
        }

        .stat + .stat { border-left: 1px solid var(--border); }

        .stat-num {
            font-size: 26px; font-weight: 800; line-height: 1;
            margin-bottom: 5px;
        }

        .stat-lbl {
            font-size: 10.5px; font-weight: 500;
            color: var(--ink-muted); line-height: 1.4;
        }

        /* Progress bar lunas */
        .progress-wrap { padding: 0 16px; margin-top: 12px; }

        .progress-bar-bg {
            height: 6px; border-radius: 99px;
            background: var(--border); overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%; border-radius: 99px;
            background: linear-gradient(90deg, var(--green), #22c55e);
            transition: width .6s ease;
        }

        .progress-label {
            display: flex; justify-content: space-between;
            font-size: 10.5px; color: var(--ink-muted);
            margin-top: 5px;
        }

        /* ─── SECTION ───────────────────────────────────────────── */
        .section { padding: 20px 16px 0; }

        .section-title {
            font-size: 10.5px; font-weight: 700;
            color: var(--ink-muted); text-transform: uppercase;
            letter-spacing: .09em; margin-bottom: 10px;
            display: flex; align-items: center; gap: 6px;
        }

        .section-title::after {
            content: ''; flex: 1; height: 1px;
            background: var(--border);
        }

        /* ─── KARTU BULAN ───────────────────────────────────────── */
        .bulan-list { display: flex; flex-direction: column; gap: 8px; }

        .bulan-card {
            background: var(--card);
            border-radius: var(--r);
            border: 1.5px solid var(--border);
            overflow: hidden; cursor: pointer;
            transition: box-shadow .15s;
        }

        .bulan-card:active { box-shadow: 0 2px 8px rgba(0,0,0,.08); }

        .bulan-card.lunas { border-color: var(--green-ring); }
        .bulan-card.belum { opacity: .72; }

        .bulan-header {
            display: flex; align-items: center;
            padding: 11px 13px; gap: 11px;
        }

        .bulan-no {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 800; flex-shrink: 0;
        }

        .lunas .bulan-no { background: var(--green-light); color: var(--green); }
        .belum .bulan-no { background: #f1f5f9; color: var(--ink-faint); }

        .bulan-info { flex: 1; min-width: 0; }

        .bulan-nama {
            font-size: 14px; font-weight: 700;
            color: var(--ink); line-height: 1.2;
        }

        .bulan-sub {
            font-size: 11px; color: var(--ink-muted);
            margin-top: 2px; font-family: 'DM Mono', monospace;
        }

        .bulan-badge {
            font-size: 11px; font-weight: 700;
            padding: 3px 10px; border-radius: 20px;
            flex-shrink: 0;
        }

        .badge-lunas { background: var(--green-light); color: var(--green); }
        .badge-belum { background: #f1f5f9; color: var(--ink-muted); }

        .chevron {
            color: var(--ink-faint); flex-shrink: 0;
            transition: transform .2s; margin-left: 4px;
        }

        .bulan-card.open .chevron { transform: rotate(180deg); }

        /* Detail expand */
        .bulan-detail {
            border-top: 1px solid var(--border);
            padding: 10px 13px;
            background: #fafcff;
            display: none;
        }

        .bulan-card.lunas .bulan-detail {
            background: #f0fdf4;
            border-top-color: var(--green-ring);
        }

        .bulan-card.open .bulan-detail { display: block; }

        .d-row {
            display: flex; justify-content: space-between;
            align-items: center; font-size: 12px;
            padding: 3px 0;
        }

        .d-lbl { color: var(--ink-muted); display: flex; align-items: center; gap: 5px; }

        .d-val {
            font-family: 'DM Mono', monospace;
            font-weight: 500; color: var(--ink-soft);
        }

        .d-minus      { color: var(--red) !important; }
        .d-plus       { color: #0369a1 !important; }
        .d-minus-amber{ color: var(--amber) !important; }

        .d-badge {
            font-size: 9px; font-weight: 700; letter-spacing: .03em;
            padding: 1px 6px; border-radius: 4px;
            text-transform: uppercase;
        }
        .d-badge-red   { background: var(--red-light);   color: var(--red); }
        .d-badge-blue  { background: var(--blue-light);  color: var(--blue); }
        .d-badge-amber { background: var(--amber-light); color: var(--amber); }

        .d-row.d-subtotal {
            border-top: 1px dashed var(--border);
            margin-top: 4px; padding-top: 6px;
        }
        .d-row.d-subtotal .d-lbl { color: var(--ink-soft); font-weight: 600; }
        .d-row.d-subtotal .d-val { color: var(--ink-soft); font-weight: 600; }

        .d-row.d-total {
            border-top: 1px dashed var(--border);
            margin-top: 5px; padding-top: 7px;
        }

        .d-row.d-total .d-lbl { font-weight: 700; color: var(--ink); }
        .d-row.d-total .d-val { font-weight: 700; color: var(--green); font-size: 13px; }

        .kode-chip {
            display: inline-block; margin-top: 6px;
            font-size: 10px; font-weight: 600;
            font-family: 'DM Mono', monospace;
            background: var(--blue-light); color: var(--blue);
            border-radius: 6px; padding: 2px 8px;
        }

        /* ─── FOOTER ────────────────────────────────────────────── */
        .page-footer {
            margin: 24px 16px 36px;
            text-align: center;
        }

        .footer-seal {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 11px; color: var(--ink-faint);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px; padding: 6px 14px;
        }

        .footer-time {
            margin-top: 6px; font-size: 10px;
            color: var(--ink-faint);
        }

        /* ─── Animasi ────────────────────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .stats-wrap    { animation: fadeUp .4s ease both .1s; }
        .progress-wrap { animation: fadeUp .4s ease both .15s; }
        .section       { animation: fadeUp .4s ease both .2s; }

        .bulan-list > .bulan-card:nth-child(1)  { animation: fadeUp .35s ease both .22s; }
        .bulan-list > .bulan-card:nth-child(2)  { animation: fadeUp .35s ease both .26s; }
        .bulan-list > .bulan-card:nth-child(3)  { animation: fadeUp .35s ease both .30s; }
        .bulan-list > .bulan-card:nth-child(4)  { animation: fadeUp .35s ease both .34s; }
        .bulan-list > .bulan-card:nth-child(5)  { animation: fadeUp .35s ease both .37s; }
        .bulan-list > .bulan-card:nth-child(6)  { animation: fadeUp .35s ease both .40s; }
        .bulan-list > .bulan-card:nth-child(n+7){ animation: fadeUp .35s ease both .43s; }
    </style>
</head>
<body>

{{-- ── HERO ─────────────────────────────────────────────────────────────── --}}
<div class="hero">
    <div class="hero-header">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" class="hero-logo" alt="{{ $namaSekolah }}">
            @else
            <div class="hero-logo-ph">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="1.5">
                    <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5a12.083 12.083 0 01-6.16-10.922L12 14z"/>
                </svg>
            </div>
        @endif

        <div>
            @if($namaYayasan)
                <div class="school-sub">{{ $namaYayasan }}</div>
            @endif
            <div class="school-name">{{ $namaSekolah }}</div>
        </div>

        <div class="ta-badge">T.A. {{ $tahunNama }}</div>
    </div>

    <div class="hero-body">
        <div class="hero-label">Riwayat Pembayaran SPP</div>
        <div class="hero-name">{{ $siswa->nama }}</div>
        <div class="hero-chips">
            @if(!empty($siswa->id_siswa))
                <div class="chip">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="7" y1="9" x2="17" y2="9"/><line x1="7" y1="13" x2="13" y2="13"/></svg>
                    {{ $siswa->id_siswa }}
                </div>
            @endif
            <div class="chip">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Kelas {{ $kelasNama }}
            </div>
            
        </div>
    </div>
</div>

{{-- ── STATS CARD ──────────────────────────────────────────────────────── --}}
<div class="stats-wrap">
    <div class="stats-card">
        <div class="stat">
            <div class="stat-num" style="color:var(--green);">{{ $totalLunas }}</div>
            <div class="stat-lbl">Bulan<br>Lunas</div>
        </div>
        <div class="stat">
            <div class="stat-num" style="color:var(--red);">{{ 12 - $totalLunas }}</div>
            <div class="stat-lbl">Belum<br>Dibayar</div>
        </div>
        <div class="stat">
            <div class="stat-num" style="color:var(--navy);">12</div>
            <div class="stat-lbl">Total<br>Bulan</div>
        </div>
    </div>
</div>

{{-- ── PROGRESS BAR ────────────────────────────────────────────────────── --}}
<div class="progress-wrap">
    @php $pct = round($totalLunas / 12 * 100); @endphp
    <div class="progress-bar-bg">
        <div class="progress-bar-fill" style="width: {{ $pct }}%"></div>
    </div>
    <div class="progress-label">
        <span>Progress Pembayaran</span>
        <span style="font-weight:700;color:var(--green);">{{ $pct }}%</span>
    </div>
</div>

{{-- ── DAFTAR BULAN ────────────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-title">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        Detail per Bulan
    </div>

    <div class="bulan-list">
        @foreach($riwayat as $i => $bln)
        <div class="bulan-card {{ $bln['sudah_bayar'] ? 'lunas' : 'belum' }}"
             id="bc-{{ $i }}" onclick="toggle({{ $i }})">

            <div class="bulan-header">
                <div class="bulan-no">{{ $bln['no_str'] }}</div>

                <div class="bulan-info">
                    <div class="bulan-nama">{{ $bln['nama_bulan'] }}</div>
                    <div class="bulan-sub">
                        @if($bln['sudah_bayar'] && $bln['tanggal'])
                            Dibayar {{ $bln['tanggal'] }}
                        @elseif($bln['sudah_bayar'])
                            Sudah Lunas
                        @else
                            Belum dibayar
                        @endif
                    </div>
                </div>

                <span class="bulan-badge {{ $bln['sudah_bayar'] ? 'badge-lunas' : 'badge-belum' }}">
                    {{ $bln['sudah_bayar'] ? 'Lunas' : 'Belum' }}
                </span>

                <svg class="chevron" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </div>

            <div class="bulan-detail">
                @php
                    $isTK     = strtoupper($siswa->jenjang) === 'TK';
                    $spp      = $bln['spp'];
                    $donatur  = $bln['donatur'];
                    $mamin    = $isTK ? $bln['mamin'] : 0;
                    $kredit   = $bln['kredit'];
                    $tagihan  = max(0, $spp - $donatur + $mamin);
                    $totalByr = max(0, $tagihan - $kredit);
                @endphp

                {{-- SPP / bulan --}}
                <div class="d-row">
                    <span class="d-lbl">SPP / bulan</span>
                    <span class="d-val">Rp {{ number_format($spp, 0, ',', '.') }}</span>
                </div>

                {{-- Donatur — selalu tampil --}}
                <div class="d-row">
                    <span class="d-lbl">
                        Donatur
                        
                    </span>
                    <span class="d-val d-minus">
                        &minus; Rp {{ number_format($donatur, 0, ',', '.') }}
                    </span>
                </div>

                {{-- Mamin — selalu tampil untuk TK --}}
                @if($isTK)
                <div class="d-row">
                    <span class="d-lbl">
                        Mamin
                    </span>
                    <span class="d-val d-plus">+ Rp {{ number_format($mamin, 0, ',', '.') }}</span>
                </div>
                @endif

                {{-- Tagihan / bulan: SPP − Donatur + Mamin --}}
                <div class="d-row d-subtotal">
                    <span class="d-lbl">Tagihan / bulan</span>
                    <span class="d-val">Rp {{ number_format($tagihan, 0, ',', '.') }}</span>
                </div>

                {{-- Kredit — hanya jika dipakai --}}
                @if($kredit > 0)
                <div class="d-row">
                    <span class="d-lbl">
                        Kredit Digunakan
                        
                    </span>
                    <span class="d-val d-minus-amber">&minus; Rp {{ number_format($kredit, 0, ',', '.') }}</span>
                </div>
                @endif

                {{-- TOTAL --}}
                <div class="d-row d-total">
                    @if($bln['sudah_bayar'])
                        <span class="d-lbl">Dibayarkan</span>
                        <span class="d-val" style="color:var(--green);">
                            Rp {{ number_format($bln['yang_dibayar'], 0, ',', '.') }}
                        </span>
                    @else
                        <span class="d-lbl">Estimasi Tagihan</span>
                        <span class="d-val" style="color:var(--ink-muted);">
                            Rp {{ number_format($totalByr, 0, ',', '.') }}
                        </span>
                    @endif
                </div>

                {{-- Kode bayar --}}
                @if($bln['kode_bayar'])
                    <div class="kode-chip">🔖 {{ $bln['kode_bayar'] }}</div>
                @endif
            </div>

        </div>
        @endforeach
    </div>
</div>

{{-- ── FOOTER ──────────────────────────────────────────────────────────── --}}
<div class="page-footer">
    <div class="footer-seal">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
        Data resmi — diperbarui otomatis
    </div>
    <div class="footer-time">
        Dilihat pada {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
    </div>
</div>

<script>
function toggle(i) {
    const card = document.getElementById('bc-' + i);
    card.classList.toggle('open');
}
</script>
</body>
</html>