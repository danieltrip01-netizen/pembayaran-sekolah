<?php

namespace App\Http\Controllers;

use App\Models\KreditLog;
use App\Models\Pembayaran;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembayaranController extends Controller
{
    // ─── Index ───────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user    = Auth::user();
        $jenjang = $user->jenjang;

        $query = Pembayaran::with(['siswa', 'user'])
            ->when($jenjang, fn($q) => $q->whereHas('siswa', fn($sq) => $sq->where('jenjang', $jenjang)));

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_bayar', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_bayar', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', fn($sq) => $sq->where('nama', 'like', "%{$search}%"))
                  ->orWhere('kode_bayar', 'like', "%{$search}%");
            });
        }
        if ($request->filled('jenjang') && !$jenjang) {
            $query->whereHas('siswa', fn($q) => $q->where('jenjang', $request->jenjang));
        }
        if ($request->filled('status_setor')) {
            if ($request->status_setor === 'belum') {
                $query->whereNull('setoran_id');
            } elseif ($request->status_setor === 'sudah') {
                $query->whereNotNull('setoran_id');
            }
        }

        $pembayaran = $query->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('pembayaran.index', compact('pembayaran'));
    }

    // ─── Create ──────────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $user    = Auth::user();
        $jenjang = $user->jenjang;

        $siswa = $request->filled('siswa_id') ? Siswa::find($request->siswa_id) : null;

        $daftarSiswa = Siswa::aktif()
            ->when($jenjang, fn($q) => $q->jenjang($jenjang))
            ->orderBy('jenjang')
            ->orderBy('kelas')
            ->orderBy('nama')
            ->get();

        $namaBulan = [
            1  => 'Januari',   2  => 'Februari', 3  => 'Maret',
            4  => 'April',     5  => 'Mei',       6  => 'Juni',
            7  => 'Juli',      8  => 'Agustus',   9  => 'September',
            10 => 'Oktober',  11  => 'November', 12  => 'Desember',
        ];

        $tahunSekarang = (int) date('Y');
        $tahunList     = range($tahunSekarang - 2, $tahunSekarang + 1);
        $tahunAjaran   = $this->getTahunAjaran();

        return view('pembayaran.create', compact(
            'daftarSiswa',
            'siswa',
            'namaBulan',
            'tahunList',
            'tahunAjaran'
        ));
    }

    // ─── Store ───────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'siswa_id'      => ['required', 'integer', 'exists:siswa,id'],
            'bulan_bayar'   => ['required', 'array', 'min:1'],
            'bulan_bayar.*' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'tanggal_bayar' => ['required', 'date'],
            'keterangan'    => ['nullable', 'string', 'max:255'],
        ], [
            'siswa_id.required'      => 'Siswa wajib dipilih.',
            'siswa_id.exists'        => 'Siswa tidak ditemukan.',
            'bulan_bayar.required'   => 'Pilih minimal 1 bulan pembayaran.',
            'bulan_bayar.min'        => 'Pilih minimal 1 bulan pembayaran.',
            'tanggal_bayar.required' => 'Tanggal bayar wajib diisi.',
        ]);

        return DB::transaction(function () use ($data) {

            // Lock row agar tidak ada race condition saldo kredit
            $siswa = Siswa::lockForUpdate()->findOrFail($data['siswa_id']);
            $siswa->load('pembayaran');

            // Normalize & urutkan bulan yang dipilih
            $bulanBaru = array_values(array_unique($data['bulan_bayar']));
            sort($bulanBaru);

            // Cek duplikat bulan
            $bulanSudahBayar = $this->extractBulanDibayar($siswa->pembayaran);
            $sudahBayar      = array_intersect($bulanBaru, $bulanSudahBayar);

            if (!empty($sudahBayar)) {
                $label = implode(', ', array_map(fn($b) => $this->formatBulan($b), $sudahBayar));
                throw ValidationException::withMessages([
                    'bulan_bayar' => "Bulan berikut sudah dibayar: {$label}",
                ]);
            }

            $jumlahBulan = count($bulanBaru);
            $spp         = (float) $siswa->nominal_pembayaran;
            $donatur     = (float) $siswa->nominal_donator;
            $mamin       = $siswa->jenjang === 'TK' ? (float) $siswa->nominal_mamin : 0;

            // RUMUS: (SPP - Donatur + Mamin) × jumlah_bulan
            $tagiBruto    = ($spp - $donatur + $mamin) * $jumlahBulan;
            $kreditDiskon = min((int) $siswa->saldo_kredit, (int) $tagiBruto);
            $totalBayar   = max(0, $tagiBruto - $kreditDiskon);

            $pembayaran = Pembayaran::create([
                'kode_bayar'        => Pembayaran::generateKodeBayar(),
                'siswa_id'          => $siswa->id,
                'user_id'           => Auth::id(),
                'tanggal_bayar'     => $data['tanggal_bayar'],
                'bulan_bayar'       => $bulanBaru,
                'jumlah_bulan'      => $jumlahBulan,
                'nominal_per_bulan' => $spp,
                'nominal_mamin'     => $mamin * $jumlahBulan,
                'nominal_donator'   => $donatur * $jumlahBulan,
                'kredit_digunakan'  => $kreditDiskon,
                'total_bayar'       => $totalBayar,
                'status'            => 'lunas',
                'keterangan'        => $data['keterangan'] ?? null,
            ]);

            // Kurangi saldo kredit & catat log
            if ($kreditDiskon > 0) {
                $siswa->pakaiKredit(
                    jumlah       : $kreditDiskon,
                    pembayaranId : $pembayaran->id,
                    keterangan   : 'Kredit dipakai untuk ' . $pembayaran->kode_bayar,
                );
            }

            $pesan = 'Pembayaran berhasil disimpan. Kode: ' . $pembayaran->kode_bayar;
            if ($kreditDiskon > 0) {
                $pesan .= '. Kredit Rp ' . number_format($kreditDiskon, 0, ',', '.')
                        . ' telah dipotongkan dari tagihan.';
            }

            return redirect()->route('pembayaran.show', $pembayaran)
                ->with('success', $pesan);
        });
    }

    // ─── Show ────────────────────────────────────────────────────────────────

    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load(['siswa', 'user', 'setoran', 'kreditLog.user']);
        return view('pembayaran.show', compact('pembayaran'));
    }

    // ─── Edit ────────────────────────────────────────────────────────────────

    public function edit(Pembayaran $pembayaran)
    {
        if ($pembayaran->setoran_id) {
            return redirect()->route('pembayaran.show', $pembayaran)
                ->with('error', 'Pembayaran yang sudah masuk setoran tidak dapat diedit.');
        }

        $pembayaran->load('siswa');
        return view('pembayaran.edit', compact('pembayaran'));
    }

    // ─── Update ──────────────────────────────────────────────────────────────

    public function update(Request $request, Pembayaran $pembayaran)
    {
        if ($pembayaran->setoran_id) {
            return back()->with('error', 'Pembayaran yang sudah masuk setoran tidak dapat diedit.');
        }

        $request->validate([
            'tanggal_bayar'   => ['required', 'date'],
            'nominal_donator' => ['nullable', 'numeric', 'min:0'],
            'keterangan'      => ['nullable', 'string', 'max:255'],
        ]);

        $jumlahBulan     = $pembayaran->jumlah_bulan;
        $spp             = (float) $pembayaran->nominal_per_bulan;
        $donaturPerBulan = (float) ($request->nominal_donator ?? 0);
        $maminPerBulan   = $jumlahBulan > 0
            ? (float) $pembayaran->nominal_mamin / $jumlahBulan
            : 0;

        // RUMUS: (SPP - Donatur + Mamin) × jumlah_bulan
        $tagiBruto  = ($spp - $donaturPerBulan + $maminPerBulan) * $jumlahBulan;
        $kredit     = (int) ($pembayaran->kredit_digunakan ?? 0);
        $totalBayar = max(0, $tagiBruto - $kredit);

        $pembayaran->update([
            'tanggal_bayar'   => $request->tanggal_bayar,
            'nominal_donator' => $donaturPerBulan * $jumlahBulan,
            'total_bayar'     => $totalBayar,
            'keterangan'      => $request->keterangan,
        ]);

        return redirect()->route('pembayaran.show', $pembayaran)
            ->with('success', 'Data pembayaran berhasil diperbarui.');
    }

    // ─── Destroy ─────────────────────────────────────────────────────────────

    public function destroy(Pembayaran $pembayaran)
    {
        if ($pembayaran->setoran_id) {
            return back()->with('error', 'Pembayaran ini sudah masuk setoran. Hapus setoran terlebih dahulu.');
        }

        DB::transaction(function () use ($pembayaran) {
            $kreditKembali = (int) ($pembayaran->kredit_digunakan ?? 0);

            if ($kreditKembali > 0) {
                $pembayaran->siswa->tambahKredit(
                    jumlah     : $kreditKembali,
                    keterangan : 'Kredit dikembalikan karena pembayaran ' . $pembayaran->kode_bayar . ' dihapus',
                );
            }

            $pembayaran->delete();
        });

        return redirect()->route('pembayaran.index')
            ->with('success', 'Pembayaran ' . $pembayaran->kode_bayar . ' berhasil dihapus.');
    }

    // ─── AJAX ────────────────────────────────────────────────────────────────

    /**
     * GET /siswa/{siswa}/data
     * Dipakai oleh form create (JavaScript fetch) untuk memuat info siswa + status bulan.
     */
    public function getSiswaData(Siswa $siswa)
    {
        // Pastikan user hanya bisa akses siswa sesuai jenjangnya
        $userJenjang = Auth::user()->jenjang;
        if ($userJenjang && $siswa->jenjang !== $userJenjang) {
            return response()->json(['message' => 'Akses tidak diizinkan.'], 403);
        }

        $siswa->load('pembayaran');

        $tahunAjaran  = $this->getTahunAjaran();
        $bulanAktif   = $siswa->getBulanAktif($tahunAjaran);
        $bulanDibayar = $this->extractBulanDibayar($siswa->pembayaran);
        $bulanBelum   = array_values(array_diff($bulanAktif, $bulanDibayar));

        return response()->json([
            'siswa'         => $siswa,
            'bulan_aktif'   => $bulanAktif,
            'bulan_dibayar' => $bulanDibayar,
            'bulan_belum'   => $bulanBelum,
            'saldo_kredit'  => (int) $siswa->saldo_kredit,
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function getTahunAjaran(): int
    {
        return (int) date('m') >= 7 ? (int) date('Y') : (int) date('Y') - 1;
    }

    private function extractBulanDibayar($pembayaranCollection): array
    {
        $result = [];
        foreach ($pembayaranCollection as $p) {
            $raw   = $p->getRawOriginal('bulan_bayar');
            $bulan = Siswa::safeDecode($raw);
            foreach ($bulan as $b) {
                if (!empty($b) && is_string($b)) {
                    $result[] = $b;
                }
            }
        }
        return array_values(array_unique($result));
    }

    private function formatBulan(string $bulan): string
    {
        $nama = [
            '01' => 'Januari',   '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',     '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',      '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober',   '11' => 'November',  '12' => 'Desember',
        ];
        [$tahun, $bln] = explode('-', $bulan, 2);
        return ($nama[$bln] ?? $bln) . ' ' . $tahun;
    }
}