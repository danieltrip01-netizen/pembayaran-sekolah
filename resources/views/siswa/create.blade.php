{{-- resources/views/siswa/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Tambah Siswa')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.index') }}">Data Siswa</a></li>
    <li class="breadcrumb-item active">Tambah Siswa</li>
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--navy); font-family:'Sora',sans-serif;">Tambah Siswa Baru</h4>
            <p class="mb-0" style="color:var(--ink-muted);font-size:.85rem;">Daftarkan siswa baru ke sistem</p>
        </div>
        <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @if (!$tahunPelajaran)
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>Tidak ada tahun pelajaran aktif. Siswa bisa ditambahkan, namun
                <strong>penempatan kelas & SPP</strong> tidak dapat diisi.
                Silakan aktifkan tahun pelajaran terlebih dahulu.
            </div>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xl-8">

            <form method="POST" action="{{ route('siswa.store') }}" id="formSiswa">
                @csrf

                {{-- ── Informasi Dasar ─────────────────────────────────────────── --}}
                <div class="card mb-3">
                    <div class="card-header"
                        style="background: var(--navy); color: #fff; border-radius: var(--r-xl) var(--r-xl) 0 0 !important;">
                        <h6 class="mb-0 fw-bold" style="color:#fff;">
                            <i class="bi bi-person-badge me-2"></i>Informasi Siswa
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            {{-- ID Siswa --}}
                            <div class="col-md-4">
                                <label class="form-label">ID Siswa</label>
                                <div class="input-group">
                                    <input type="text" name="id_siswa" id="idSiswa"
                                        value="{{ old('id_siswa', $idSiswa) }}"
                                        class="form-control @error('id_siswa') is-invalid @enderror"
                                        placeholder="Auto-generate">
                                    <button type="button" class="btn btn-outline-secondary" id="btnRegenId"
                                        title="Generate ulang ID">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </div>
                                @error('id_siswa')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Kosongkan untuk auto-generate.</div>
                            </div>

                            {{-- Nama --}}
                            <div class="col-md-8">
                                <label class="form-label">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nama" value="{{ old('nama') }}"
                                    class="form-control @error('nama') is-invalid @enderror"
                                    placeholder="Nama lengkap siswa" required autofocus>
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Jenjang --}}
                            <div class="col-md-4">
                                <label class="form-label">
                                    Jenjang <span class="text-danger">*</span>
                                </label>
                                <select name="jenjang" id="selectJenjang"
                                    class="form-select @error('jenjang') is-invalid @enderror"
                                    {{ $userJenjang ? 'disabled' : '' }} required>
                                    @if ($userJenjang)
                                        <option value="{{ $userJenjang }}" selected>{{ $userJenjang }}</option>
                                    @else
                                        <option value="">— Pilih Jenjang —</option>
                                        <option value="TK" {{ old('jenjang', $jenjang) == 'TK' ? 'selected' : '' }}>TK
                                            / PAUD</option>
                                        <option value="SD" {{ old('jenjang', $jenjang) == 'SD' ? 'selected' : '' }}>SD
                                        </option>
                                        <option value="SMP" {{ old('jenjang', $jenjang) == 'SMP' ? 'selected' : '' }}>
                                            SMP</option>
                                    @endif
                                </select>
                                @if ($userJenjang)
                                    <input type="hidden" name="jenjang" value="{{ $userJenjang }}">
                                @endif
                                @error('jenjang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>
                                        ✅ Aktif</option>
                                    <option value="tidak_aktif" {{ old('status') == 'tidak_aktif' ? 'selected' : '' }}>⛔
                                        Tidak Aktif</option>
                                </select>
                            </div>

                            {{-- No HP Wali --}}
                            <div class="col-md-4">
                                <label class="form-label">
                                    No HP Wali
                                    <small class="fw-normal" style="color:var(--ink-muted);">(Opsional)</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-whatsapp" style="color:#25D366;"></i>
                                    </span>
                                    <input type="text" name="no_hp_wali"
                                           value="{{ old('no_hp_wali') }}"
                                           class="form-control @error('no_hp_wali') is-invalid @enderror"
                                           placeholder="08xxxxxxxxxx">
                                </div>
                                @error('no_hp_wali')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Digunakan untuk notifikasi WhatsApp.</div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ── Penempatan Kelas & SPP (SiswaKelas) ──────────────────────── --}}
                @if ($tahunPelajaran)
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                                <i class="bi bi-mortarboard me-2"></i>Penempatan Kelas
                                <span class="badge ms-2"
                                    style="background:var(--blue-pale);color:var(--blue-dark);
                             border:1px solid var(--blue-light);font-size:.72rem;font-weight:600;">
                                    T.A. {{ $tahunPelajaran->nama }}
                                </span>
                            </h6>
                        </div>
                        <div class="card-body">
                            {{-- Hidden tahun_pelajaran_id --}}
                            <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunPelajaran->id }}">

                            <div class="row g-3">

                                {{-- Kelas (dari tabel kelas, difilter JS sesuai jenjang) --}}
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Kelas <span class="text-danger">*</span>
                                    </label>
                                    <select name="kelas_id" id="selectKelas"
                                        class="form-select @error('kelas_id') is-invalid @enderror" required>
                                        <option value="">— Pilih Kelas —</option>
                                    </select>
                                    @error('kelas_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- SPP --}}
                                <div class="col-md-6">
                                    <label class="form-label">
                                        SPP / Bulan <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="nominal_spp" id="inSPP"
                                            value="{{ old('nominal_spp', 0) }}"
                                            class="form-control @error('nominal_spp') is-invalid @enderror" min="0"
                                            step="1000">
                                    </div>
                                    @error('nominal_spp')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Donatur --}}
                                <div class="col-md-6">
                                    <label class="form-label">Donatur / Bulan <small
                                            class="text-muted fw-400">(pengurang)</small></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="nominal_donator" id="inDonatur"
                                            value="{{ old('nominal_donator', 0) }}" class="form-control" min="0"
                                            step="1000">
                                    </div>
                                </div>

                                {{-- Mamin (hanya TK) --}}
                                <div class="col-md-6" id="rowMamin"
                                    style="{{ old('jenjang', $jenjang) === 'TK' ? '' : 'display:none' }}">
                                    <label class="form-label">
                                        Mamin / Bulan
                                        <span class="badge ms-1"
                                            style="background:var(--yellow-pale);color:#B45309;border:1px solid #FDE68A;
                                     font-size:.62rem;font-weight:600;">Khusus
                                            TK</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="nominal_mamin" id="inMamin"
                                            value="{{ old('nominal_mamin', 5000) }}" class="form-control" min="0"
                                            step="1000">
                                    </div>
                                </div>

                                {{-- Preview Total --}}
                                <div class="col-12">
                                    <div class="rounded-3 p-3"
                                        style="background:var(--blue-pale);border:1px solid var(--blue-light);">
                                        <div class="d-flex gap-4 flex-wrap" style="font-size:.85rem;">
                                            <div>
                                                <span style="color:var(--ink-muted);">SPP:</span>
                                                <strong id="prevSPP" class="ms-1" style="color:var(--navy);">Rp
                                                    0</strong>
                                            </div>
                                            <span style="color:var(--red);">−</span>
                                            <div>
                                                <span style="color:var(--ink-muted);">Donatur:</span>
                                                <strong id="prevDonatur" class="ms-1" style="color:var(--red);">Rp
                                                    0</strong>
                                            </div>
                                            <div id="prevMaminWrap"
                                                style="{{ old('jenjang', $jenjang) === 'TK' ? '' : 'display:none' }}"
                                                class="d-flex align-items-center gap-2">
                                                <span style="color:var(--green);">+</span>
                                                <span style="color:var(--ink-muted);">Mamin:</span>
                                                <strong id="prevMamin" class="ms-1" style="color:#0369a1;">Rp
                                                    0</strong>
                                            </div>
                                            <div>
                                                <span style="color:var(--ink-muted);">=</span>
                                                <span style="color:var(--ink-muted);">Total Tagihan/bln:</span>
                                                <strong id="prevTotal" class="ms-1"
                                                    style="color:var(--green);font-size:1rem;">Rp 0</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── Tanggal ──────────────────────────────────────────────────── --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                            <i class="bi bi-calendar3 me-2"></i>Tanggal
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-4">
                                <label class="form-label">
                                    Tanggal Masuk <span class="text-danger">*</span>
                                </label>
                                @php
                                    $defaultMasuk = old(
                                        'tanggal_masuk',
                                        isset($tahunPelajaran)
                                            ? $tahunPelajaran->tanggal_mulai->format('Y-m-d')
                                            : date('Y') . '-07-01',
                                    );
                                @endphp
                                <input type="date" name="tanggal_masuk" value="{{ $defaultMasuk }}"
                                    class="form-control @error('tanggal_masuk') is-invalid @enderror" required>
                                @error('tanggal_masuk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Default: awal tahun pelajaran aktif
                                    @if (isset($tahunPelajaran))
                                        ({{ $tahunPelajaran->tanggal_mulai->isoFormat('D MMMM Y') }})
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tanggal Keluar</label>
                                <input type="date" name="tanggal_keluar" value="{{ old('tanggal_keluar') }}"
                                    class="form-control @error('tanggal_keluar') is-invalid @enderror">
                                @error('tanggal_keluar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Kosongkan jika siswa masih aktif.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                                    class="form-control" placeholder="Catatan (opsional)" maxlength="255">
                            </div>

                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i>Simpan Siswa
                    </button>
                    <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>

            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Data kelas per jenjang dari server (bukan hardcoded)
        const semuaKelas = @json($semuaKelas);
        // Format: { "TK": [{id:1, nama:"KB"}, ...], "SD": [...], "SMP": [...] }

        const oldKelasId = "{{ old('kelas_id') }}";
        const oldJenjang = "{{ old('jenjang', $jenjang) }}";

        function updateKelas(jenjang, selectedId = '') {
            const sel = document.getElementById('selectKelas');
            if (!sel) return;
            sel.innerHTML = '<option value="">— Pilih Kelas —</option>';
            const kelasList = semuaKelas[jenjang] || [];
            kelasList.forEach(k => {
                const opt = document.createElement('option');
                opt.value = k.id;
                opt.textContent = k.nama;
                if (String(k.id) === String(selectedId)) opt.selected = true;
                sel.appendChild(opt);
            });
        }

        function toggleMamin(jenjang) {
            const show = jenjang === 'TK';
            const rowMamin = document.getElementById('rowMamin');
            const prevMaminWrap = document.getElementById('prevMaminWrap');
            if (rowMamin) rowMamin.style.display = show ? '' : 'none';
            if (prevMaminWrap) prevMaminWrap.style.display = show ? '' : 'none';
            if (!show) {
                const el = document.getElementById('inMamin');
                if (el) el.value = 0;
            }
            updatePreview();
        }

        function updatePreview() {
            const spp = parseFloat(document.getElementById('inSPP')?.value) || 0;
            const donatur = parseFloat(document.getElementById('inDonatur')?.value) || 0;
            const mamin = parseFloat(document.getElementById('inMamin')?.value) || 0;
            const jenjang = document.getElementById('selectJenjang')?.value || oldJenjang;
            const total = spp - donatur + (jenjang === 'TK' ? mamin : 0);

            const fmt = n => new Intl.NumberFormat('id-ID').format(Math.round(n));
            document.getElementById('prevSPP').textContent = 'Rp ' + fmt(spp);
            document.getElementById('prevDonatur').textContent = 'Rp ' + fmt(donatur);
            const pm = document.getElementById('prevMamin');
            if (pm) pm.textContent = 'Rp ' + fmt(mamin);
            document.getElementById('prevTotal').textContent = 'Rp ' + fmt(total);
        }

        // Init
        updateKelas(oldJenjang, oldKelasId);
        toggleMamin(oldJenjang);
        updatePreview();

        document.getElementById('selectJenjang')?.addEventListener('change', function() {
            updateKelas(this.value);
            toggleMamin(this.value);
            genId(this.value);
        });

        ['inSPP', 'inDonatur', 'inMamin'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', updatePreview);
        });

        // ── Generate ID ──────────────────────────────────────────────────
        async function genId(jenjang) {
            if (!jenjang) return;
            try {
                const res = await fetch(`{{ route('siswa.generate-id') }}?jenjang=${jenjang}`);
                if (res.ok) {
                    const data = await res.json();
                    document.getElementById('idSiswa').value = data.id_siswa;
                }
            } catch (e) {
                /* silent */ }
        }

        document.getElementById('btnRegenId').addEventListener('click', function() {
            const jenjang = document.getElementById('selectJenjang')?.value ||
                "{{ $userJenjang ?? 'SD' }}";
            genId(jenjang);
        });
    </script>
@endpush