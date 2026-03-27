{{-- resources/views/siswa/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Edit Siswa - ' . $siswa->nama)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.index') }}">Data Siswa</a></li>
    <li class="breadcrumb-item"><a href="{{ route('siswa.show', $siswa) }}">{{ $siswa->nama }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

    @php
        // Ambil record siswa_kelas untuk tahun pelajaran aktif (sudah di-load controller)
        $ka = $siswa->kelasAktif;
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--navy); font-family:'Sora',sans-serif;">Edit Data Siswa</h4>
            <p class="mb-0" style="color:var(--ink-muted);font-size:.85rem;">
                <code style="font-size:.8rem;color:var(--navy);">{{ $siswa->id_siswa }}</code>
                <span class="mx-1">—</span>
                <span class="badge-{{ strtolower($siswa->jenjang) }}">Kelas {{ $ka->kelas->nama }}</span>
            </p>
        </div>
        <a href="{{ route('siswa.show', $siswa) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8">

            <form method="POST" action="{{ route('siswa.update', $siswa) }}">
                @csrf
                @method('PUT')

                {{-- ── Informasi Siswa ─────────────────────────────────────────── --}}
                <div class="card mb-3">
                    <div class="card-header"
                        style="background: var(--navy); color: #fff; border-radius: var(--r-xl) var(--r-xl) 0 0 !important;">
                        <h6 class="mb-0 fw-bold" style="color:#fff;">
                            <i class="bi bi-person-badge me-2"></i>Informasi Siswa
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-4">
                                <label class="form-label">ID Siswa</label>
                                <input type="text" class="form-control"
                                    style="background:var(--bg);color:var(--ink-muted);" value="{{ $siswa->id_siswa }}"
                                    readonly>
                                <div class="form-text">ID tidak dapat diubah.</div>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nama" value="{{ old('nama', $siswa->nama) }}"
                                    class="form-control @error('nama') is-invalid @enderror" required>
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Jenjang <span class="text-danger">*</span></label>
                                <select name="jenjang" id="selectJenjang"
                                    class="form-select @error('jenjang') is-invalid @enderror"
                                    {{ auth()->user()->jenjang ? 'disabled' : '' }} required>
                                    <option value="TK"
                                        {{ old('jenjang', $siswa->jenjang) == 'TK' ? 'selected' : '' }}>TK / PAUD</option>
                                    <option value="SD"
                                        {{ old('jenjang', $siswa->jenjang) == 'SD' ? 'selected' : '' }}>SD</option>
                                    <option value="SMP"
                                        {{ old('jenjang', $siswa->jenjang) == 'SMP' ? 'selected' : '' }}>SMP</option>
                                </select>
                                @if (auth()->user()->jenjang)
                                    <input type="hidden" name="jenjang" value="{{ $siswa->jenjang }}">
                                @endif
                                @error('jenjang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" id="selectStatus" class="form-select">
                                    <option value="aktif"
                                        {{ old('status', $siswa->status) == 'aktif' ? 'selected' : '' }}>✅ Aktif
                                    </option>
                                    <option value="tidak_aktif"
                                        {{ old('status', $siswa->status) == 'tidak_aktif' ? 'selected' : '' }}>⛔ Tidak
                                        Aktif</option>
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
                                        value="{{ old('no_hp_wali', $siswa->no_hp_wali) }}"
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

                {{-- ── SPP Tahun Pelajaran Aktif (SiswaKelas) ──────────────────── --}}
                @if ($tahunPelajaran)
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                                <i class="bi bi-cash-coin me-2"></i>SPP & Kelas
                                <span class="badge ms-2"
                                    style="background:var(--blue-pale);color:var(--blue-dark);
                             border:1px solid var(--blue-light);font-size:.72rem;font-weight:600;">
                                    T.A. {{ $tahunPelajaran->nama }}
                                </span>
                            </h6>
                            @if (!$ka)
                                <span class="badge"
                                    style="background:#fff3cd;color:#856404;border:1px solid #ffc107;font-size:.72rem;">
                                    <i class="bi bi-info-circle me-1"></i>Belum ada data kelas untuk tahun ini
                                </span>
                            @endif
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunPelajaran->id }}">

                            <div class="row g-3">

                                {{-- Kelas --}}
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
                                    <label class="form-label">SPP / Bulan <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="nominal_spp" id="inSPP"
                                            value="{{ old('nominal_spp', (int) ($ka->nominal_spp ?? 0)) }}"
                                            class="form-control @error('nominal_spp') is-invalid @enderror" min="0"
                                            step="1000">
                                    </div>
                                    @error('nominal_spp')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Donatur --}}
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Donatur / Bulan
                                        <small style="color:var(--ink-muted);font-weight:400;">(pengurang)</small>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="nominal_donator" id="inDonatur"
                                            value="{{ old('nominal_donator', (int) ($ka->nominal_donator ?? 0)) }}"
                                            class="form-control" min="0" step="1000">
                                    </div>
                                </div>

                                {{-- ── Preview Kredit Otomatis ── --}}
                                @if ($jumlahBulanDibayar > 0)
                                    <div class="col-12">
                                        <div class="rounded-3 p-3" id="panelKreditOtomatis"
                                            style="background:#fffbeb;border:1px solid #fde68a;display:none;">
                                            <div class="d-flex align-items-start gap-2 mb-2">
                                                <i class="bi bi-stars flex-shrink-0 mt-1" style="color:#d97706;"></i>
                                                <div>
                                                    <div class="fw-bold" style="color:#92400e;font-size:.85rem;">
                                                        Kredit Otomatis Akan Digenerate
                                                    </div>
                                                    <div class="text-muted" style="font-size:.78rem;">
                                                        Donatur naik → selisih dikembalikan sebagai kredit untuk
                                                        <strong>{{ $jumlahBulanDibayar }} bulan</strong> yang sudah dibayar
                                                        di T.A. ini.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="rounded-2 p-2"
                                                style="background:rgba(255,255,255,.7);font-size:.82rem;">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span style="color:var(--ink-muted);">Donatur lama:</span>
                                                    <span>Rp <span
                                                            id="kreditDonaturLama">{{ number_format($donaturSekarang, 0, ',', '.') }}</span>/bln</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span style="color:var(--ink-muted);">Donatur baru:</span>
                                                    <span>Rp <span id="kreditDonaturBaru">0</span>/bln</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span style="color:var(--ink-muted);">Selisih / bulan:</span>
                                                    <span class="fw-bold" style="color:#059669;">+Rp <span
                                                            id="kreditSelisih">0</span></span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span style="color:var(--ink-muted);">× Bulan sudah dibayar:</span>
                                                    <span>{{ $jumlahBulanDibayar }} bulan</span>
                                                </div>
                                                <hr class="my-1" style="border-color:#fde68a;">
                                                <div class="d-flex justify-content-between">
                                                    <span class="fw-bold" style="color:#92400e;">Total kredit:</span>
                                                    <span class="fw-bold" style="color:#059669;font-size:.95rem;">
                                                        Rp <span id="kreditTotal">0</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mt-2" style="font-size:.72rem;color:#92400e;">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Kredit ini akan otomatis dipotong saat siswa melakukan pembayaran
                                                berikutnya.
                                            </div>
                                        </div>

                                        {{-- Panel info jika donatur turun --}}
                                        <div class="rounded-3 p-2 mt-1" id="panelDonaturTurun"
                                            style="background:#fff1f2;border:1px solid #fecdd3;font-size:.78rem;color:#be123c;display:none;">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            Donatur diturunkan. Tidak ada kredit otomatis — hanya berlaku untuk pembayaran
                                            ke depan.
                                        </div>
                                    </div>
                                @else
                                    <div class="col-12">
                                        <div class="rounded-3 p-2"
                                            style="background:var(--bg);border:1px solid var(--border);font-size:.78rem;color:var(--ink-muted);">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Kredit otomatis tidak berlaku — siswa belum memiliki pembayaran di tahun
                                            pelajaran ini.
                                            Perubahan donatur hanya memengaruhi tagihan ke depan.
                                        </div>
                                    </div>
                                @endif

                                {{-- Mamin (hanya TK) --}}
                                <div class="col-md-6" id="rowMamin"
                                    style="{{ old('jenjang', $siswa->jenjang) === 'TK' ? '' : 'display:none' }}">
                                    <label class="form-label">
                                        Mamin / Bulan
                                        <span class="badge ms-1"
                                            style="background:var(--yellow-pale);color:#B45309;border:1px solid #FDE68A;
                                     font-size:.6rem;font-weight:600;">Khusus
                                            TK</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="nominal_mamin" id="inMamin"
                                            value="{{ old('nominal_mamin', (int) ($ka->nominal_mamin ?? 0)) }}"
                                            class="form-control" min="0" step="1000">
                                    </div>
                                </div>

                                {{-- Preview tagihan --}}
                                <div class="col-12">
                                    <div class="rounded-3 p-3" style="background:#f0fdf4; border:1px solid #bbf7d0;">
                                        <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size:.85rem;">
                                            <span style="color:var(--ink-muted);">SPP:</span>
                                            <strong id="prevSPP" style="color:var(--navy);">Rp 0</strong>
                                            <span style="color:var(--red);">−</span>
                                            <span style="color:var(--ink-muted);">Donatur:</span>
                                            <strong id="prevDonatur" style="color:var(--red);">Rp 0</strong>
                                            @if (old('jenjang', $siswa->jenjang) === 'TK')
                                            <span style="color:var(--green);">+</span>
                                            <span style="color:var(--ink-muted);">Mamin:</span>
                                            <strong id="prevMamin" style="color:#0369a1;">Rp 0</strong>
                                            @endif
                                            <span style="color:var(--ink-muted);">=</span>
                                            <span style="color:var(--ink-muted);">Tagihan/bln:</span>
                                            <strong id="prevTotal" style="color:var(--green);font-size:1rem;">Rp
                                                0</strong>
                                        </div>
                                        <div class="mt-1" style="font-size:.72rem; color:#166534;">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Perubahan hanya berlaku untuk T.A. {{ $tahunPelajaran->nama }}.
                                            Data tahun sebelumnya tetap tersimpan.
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── Tanggal & Keterangan ─────────────────────────────────── --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold" style="color:var(--navy);">
                            <i class="bi bi-calendar3 me-2"></i>Tanggal & Keterangan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-4">
                                <label class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_masuk"
                                    value="{{ old('tanggal_masuk', $siswa->tanggal_masuk->format('Y-m-d')) }}"
                                    class="form-control @error('tanggal_masuk') is-invalid @enderror" required>
                                @error('tanggal_masuk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tanggal Keluar</label>
                                <input type="date" name="tanggal_keluar" id="inputTanggalKeluar"
                                    value="{{ old('tanggal_keluar', $siswa->tanggal_keluar?->format('Y-m-d')) }}"
                                    class="form-control">
                                <div class="form-text">Kosongkan jika masih aktif.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="keterangan"
                                    value="{{ old('keterangan', $siswa->keterangan) }}" class="form-control"
                                    placeholder="Catatan (opsional)" maxlength="255">
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ── Tombol ──────────────────────────────────────────────── --}}
                <div class="d-flex gap-2 align-items-center">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                    </button>
                    <a href="{{ route('siswa.show', $siswa) }}" class="btn btn-outline-secondary">Batal</a>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalHapus">
                            <i class="bi bi-trash me-1"></i>Hapus Siswa
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- Modal Hapus --}}
    <div class="modal fade" id="modalHapus" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                        style="width:48px;height:48px;background:#fee2e2;flex-shrink:0;">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                    </div>
                </div>
                <div class="modal-body pt-2">
                    <h5 class="fw-bold" style="color:var(--ink);">Hapus Siswa?</h5>
                    <p style="color:var(--ink-muted);margin-bottom:0;">
                        Data siswa <strong style="color:var(--ink);">{{ $siswa->nama }}</strong>
                        ({{ $siswa->id_siswa }}) akan dihapus secara permanen dan tidak dapat dikembalikan.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Batal</button>
                    <form method="POST" action="{{ route('siswa.destroy', $siswa) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash me-1"></i>Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const semuaKelas = @json($semuaKelas);
        const currentKelasId = "{{ old('kelas_id', $ka?->kelas_id ?? '') }}";
        const currentJenjang = "{{ old('jenjang', $siswa->jenjang) }}";

        // Data kredit otomatis dari controller
        const donaturLama = {{ $donaturSekarang }};
        const jumlahBulanDibayar = {{ $jumlahBulanDibayar }};

        function renderKelas(jenjang, selectedId = '') {
            const sel = document.getElementById('selectKelas');
            if (!sel) return;
            sel.innerHTML = '<option value="">— Pilih Kelas —</option>';
            (semuaKelas[jenjang] || []).forEach(k => {
                const opt = document.createElement('option');
                opt.value = k.id;
                opt.textContent = k.nama;
                if (String(k.id) === String(selectedId)) opt.selected = true;
                sel.appendChild(opt);
            });
        }

        function toggleMamin(jenjang) {
            const isTK = jenjang === 'TK';
            const rowMamin = document.getElementById('rowMamin');
            const prevMaminWrap = document.getElementById('prevMaminWrap');
            if (rowMamin) rowMamin.style.display = isTK ? '' : 'none';
            if (prevMaminWrap) prevMaminWrap.style.display = isTK ? '' : 'none';
            if (!isTK) {
                const el = document.getElementById('inMamin');
                if (el) el.value = 0;
            }
            updatePreview();
        }

        function updatePreview() {
            const spp = parseFloat(document.getElementById('inSPP')?.value) || 0;
            const donor = parseFloat(document.getElementById('inDonatur')?.value) || 0;
            const mamin = parseFloat(document.getElementById('inMamin')?.value) || 0;
            const isTK = (document.getElementById('selectJenjang')?.value || currentJenjang) === 'TK';
            const total = spp - donor + (isTK ? mamin : 0);
            const fmt = n => new Intl.NumberFormat('id-ID').format(Math.round(n));

            document.getElementById('prevSPP').textContent = 'Rp ' + fmt(spp);
            document.getElementById('prevDonatur').textContent = 'Rp ' + fmt(donor);
            const pm = document.getElementById('prevMamin');
            if (pm) pm.textContent = 'Rp ' + fmt(mamin);
            document.getElementById('prevTotal').textContent = 'Rp ' + fmt(total);

            // ── Update preview kredit otomatis ──────────────────────────────
            if (jumlahBulanDibayar > 0) {
                updatePreviewKredit(donor);
            }
        }

        function updatePreviewKredit(donaturBaru) {
            const fmt = n => new Intl.NumberFormat('id-ID').format(Math.round(n));
            const selisih = donaturBaru - donaturLama;

            const panelNaik = document.getElementById('panelKreditOtomatis');
            const panelTurun = document.getElementById('panelDonaturTurun');

            if (!panelNaik) return;

            if (selisih > 0) {
                // Donatur naik → tampilkan preview kredit
                const total = selisih * jumlahBulanDibayar;

                document.getElementById('kreditDonaturLama').textContent = fmt(donaturLama);
                document.getElementById('kreditDonaturBaru').textContent = fmt(donaturBaru);
                document.getElementById('kreditSelisih').textContent = fmt(selisih);
                document.getElementById('kreditTotal').textContent = fmt(total);

                panelNaik.style.display = '';
                if (panelTurun) panelTurun.style.display = 'none';

            } else if (selisih < 0) {
                // Donatur turun → tampilkan peringatan
                panelNaik.style.display = 'none';
                if (panelTurun) panelTurun.style.display = '';

            } else {
                // Tidak ada perubahan
                panelNaik.style.display = 'none';
                if (panelTurun) panelTurun.style.display = 'none';
            }
        }

        document.getElementById('selectJenjang')?.addEventListener('change', function() {
            renderKelas(this.value);
            toggleMamin(this.value);
        });

        document.getElementById('selectStatus')?.addEventListener('change', function() {
            if (this.value === 'aktif') {
                document.getElementById('inputTanggalKeluar').value = '';
            }
        });

        ['inSPP', 'inDonatur', 'inMamin'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', updatePreview);
        });

        renderKelas(currentJenjang, currentKelasId);
        toggleMamin(currentJenjang);
        updatePreview();
    </script>
@endpush
