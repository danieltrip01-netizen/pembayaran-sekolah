<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Http\Requests\StorePembayaranRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $user    = Auth::user();
        $jenjang = $user->jenjang;

        $query = Pembayaran::with(['siswa', 'user'])
            ->when($jenjang, fn($q) => $q->whereHas('siswa', fn($sq) => $sq->where('jenjang', $jenjang)));

        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_bayar', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_bayar', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('search')) {
            $query->whereHas('siswa', fn($q) => $q->where('nama', 'like', '%' . $request->search . '%'));
        }
        if ($request->filled('jenjang') && !$jenjang) {
            $query->whereHas('siswa', fn($q) => $q->where('jenjang', $request->jenjang));
        }
        if ($request->filled('status_setor')) {
            $query->when($request->status_setor === 'belum', fn($q) => $q->whereNull('setoran_id'));
            $query->when($request->status_setor === 'sudah', fn($q) => $q->whereNotNull('setoran_id'));
        }

        $pembayaran = $query->orderBy('id', 'desc')
                    ->paginate(20)
                    ->withQueryString();

        return view('pembayaran.index', compact('pembayaran'));
    }

    public function create(Request $request)
    {
        $user    = Auth::user();
        $jenjang = $user->jenjang;

        $selectedSiswa = $request->filled('siswa_id') ? Siswa::find($request->siswa_id) : null;

        $siswaList = Siswa::aktif()
            ->when($jenjang, fn($q) => $q->jenjang($jenjang))
            ->orderBy('jenjang')->orderBy('kelas')->orderBy('nama')
            ->get();

        $tahunAjaran = $this->getTahunAjaran();

        return view('pembayaran.create', compact('siswaList', 'selectedSiswa', 'tahunAjaran'));
    }

    public function store(StorePembayaranRequest $request)
    {
        $data  = $request->validated();
        $siswa = Siswa::with('pembayaran')->findOrFail($data['siswa_id']);

        // Cek bulan sudah dibayar
        $bulanSudahBayar = $this->extractBulanDibayar($siswa->pembayaran);
        $bulanBaru       = $data['bulan_bayar'];
        $sudahBayar      = array_intersect($bulanBaru, $bulanSudahBayar);

        if (!empty($sudahBayar)) {
            $label = implode(', ', array_map(fn($b) => $this->formatBulan($b), $sudahBayar));
            return back()
                ->withErrors(['bulan_bayar' => "Bulan berikut sudah dibayar: $label"])
                ->withInput();
        }

        $jumlahBulan = count($bulanBaru);
        $spp         = (float) $siswa->nominal_pembayaran;
        $donatur     = (float) ($data['nominal_donator'] ?? $siswa->nominal_donator);
        $mamin       = $siswa->jenjang === 'TK' ? (float) $siswa->nominal_mamin : 0;

        // ✅ RUMUS BENAR: (SPP - Donatur + Mamin) × jumlah_bulan
        $totalBayar = ($spp - $donatur + $mamin) * $jumlahBulan;

        $pembayaran = Pembayaran::create([
            'kode_bayar'        => Pembayaran::generateKodeBayar(),
            'siswa_id'          => $siswa->id,
            'user_id'           => Auth::id(),
            'tanggal_bayar'     => $data['tanggal_bayar'],
            'bulan_bayar'       => $bulanBaru,
            'jumlah_bulan'      => $jumlahBulan,
            'nominal_per_bulan' => $spp,
            'nominal_mamin'     => $mamin * $jumlahBulan,    // total mamin semua bulan
            'nominal_donator'   => $donatur * $jumlahBulan,  // total donatur semua bulan
            'total_bayar'       => $totalBayar,
            'status'            => 'lunas',
            'keterangan'        => $data['keterangan'] ?? null,
        ]);

        // ✅ FIX: route 'pembayaran.create' tidak menerima parameter model.
        //         Gunakan 'pembayaran.show' agar user langsung melihat detail pembayaran.
        return redirect()->route('pembayaran.show', $pembayaran)
                         ->with('success', 'Pembayaran berhasil disimpan. Kode: ' . $pembayaran->kode_bayar);
    }

    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load(['siswa', 'user', 'setoran']);
        return view('pembayaran.show', compact('pembayaran'));
    }

    public function edit(Pembayaran $pembayaran)
    {
        if ($pembayaran->setoran_id) {
            return redirect()->route('pembayaran.show', $pembayaran)
                ->with('error', 'Pembayaran yang sudah masuk setoran tidak dapat diedit.');
        }
        $pembayaran->load('siswa');
        return view('pembayaran.edit', compact('pembayaran'));
    }

    public function update(Request $request, Pembayaran $pembayaran)
    {
        if ($pembayaran->setoran_id) {
            return back()->with('error', 'Pembayaran yang sudah masuk setoran tidak dapat diedit.');
        }

        $request->validate([
            'tanggal_bayar'   => 'required|date',
            'nominal_donator' => 'nullable|numeric|min:0',
            'keterangan'      => 'nullable|string|max:255',
        ]);

        $jumlahBulan    = $pembayaran->jumlah_bulan;
        $spp            = (float) $pembayaran->nominal_per_bulan;
        $donaturPerBulan = (float) ($request->nominal_donator ?? 0);
        // nominal_mamin di DB sudah tersimpan sebagai total (mamin × bulan)
        $maminPerBulan  = $jumlahBulan > 0
            ? (float) $pembayaran->nominal_mamin / $jumlahBulan
            : 0;

        // ✅ RUMUS BENAR: (SPP - Donatur + Mamin) × jumlah_bulan
        $totalBayar = ($spp - $donaturPerBulan + $maminPerBulan) * $jumlahBulan;

        $pembayaran->update([
            'tanggal_bayar'   => $request->tanggal_bayar,
            'nominal_donator' => $donaturPerBulan * $jumlahBulan,
            'total_bayar'     => $totalBayar,
            'keterangan'      => $request->keterangan,
        ]);

        return redirect()->route('pembayaran.show', $pembayaran)
                         ->with('success', 'Data pembayaran berhasil diperbarui.');
    }

    public function destroy(Pembayaran $pembayaran)
    {
        if ($pembayaran->setoran_id) {
            return back()->with('error', 'Pembayaran ini sudah masuk setoran. Hapus setoran terlebih dahulu.');
        }

        $kode = $pembayaran->kode_bayar;
        $pembayaran->delete();

        return redirect()->route('pembayaran.index')
                         ->with('success', 'Pembayaran ' . $kode . ' berhasil dihapus.');
    }

    // ─── AJAX ───────────────────────────────────────────────────────

    public function getSiswaData(Siswa $siswa)
    {
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
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────

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
                if (!empty($b) && is_string($b)) $result[] = $b;
            }
        }
        return array_values(array_unique($result));
    }

    private function formatBulan(string $bulan): string
    {
        $nama = [
            '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
            '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
            '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember',
        ];
        [$tahun, $bln] = explode('-', $bulan);
        return ($nama[$bln] ?? $bln) . ' ' . $tahun;
    }
}