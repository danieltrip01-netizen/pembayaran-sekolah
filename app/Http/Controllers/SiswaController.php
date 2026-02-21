<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Http\Requests\StoreSiswaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $user    = Auth::user();
        $jenjang = $user->jenjang;

        $query = Siswa::query();

        if ($jenjang) {
            $query->where('jenjang', $jenjang);
        }
        if ($request->filled('jenjang') && !$jenjang) {
            $query->where('jenjang', $request->jenjang);
        }
        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('id_siswa', 'like', '%' . $request->search . '%');
            });
        }

        $siswa = $query
            ->withCount('pembayaran')
            ->orderBy('jenjang')->orderBy('kelas')->orderBy('nama')
            ->paginate(20)->withQueryString();

        $jenjangOptions = $jenjang ? [$jenjang] : ['TK', 'SD', 'SMP'];

        return view('siswa.index', compact('siswa', 'jenjangOptions', 'jenjang'));
    }

    public function create()
    {
        $jenjang = Auth::user()->jenjang ?? 'SD';
        $idSiswa = Siswa::generateIdSiswa($jenjang);
        return view('siswa.create', compact('jenjang', 'idSiswa'));
    }

    public function store(StoreSiswaRequest $request)
    {
        $data = $request->validated();

        if (empty($data['id_siswa'] ?? null)) {
            $data['id_siswa'] = Siswa::generateIdSiswa($data['jenjang']);
        }
        if (empty($data['tanggal_masuk'])) {
            $data['tanggal_masuk'] = date('Y') . '-07-01';
        }
        if ($data['jenjang'] !== 'TK') {
            $data['nominal_mamin'] = 0;
        }

        $siswa = Siswa::create($data);

        return redirect()->route('siswa.show', $siswa)
                         ->with('success', 'Data siswa "' . $siswa->nama . '" berhasil ditambahkan.');
    }

    public function show(Siswa $siswa)
    {
        $this->authorizeJenjang($siswa);

        $siswa->load(['pembayaran' => fn($q) => $q->with('user')->orderBy('tanggal_bayar', 'desc')]);

        $tahunAjaran = $this->getTahunAjaran();
        $bulanAktif  = $siswa->getBulanAktif($tahunAjaran);

        $bulanDibayar = $this->extractBulanDibayar($siswa->pembayaran);
        $statusBulan  = $this->hitungStatusBulan($bulanAktif, $bulanDibayar, $siswa->pembayaran);

        return view('siswa.show', compact('siswa', 'statusBulan', 'bulanAktif', 'tahunAjaran'));
    }

    public function edit(Siswa $siswa)
    {
        $this->authorizeJenjang($siswa);
        return view('siswa.edit', compact('siswa'));
    }

    public function update(StoreSiswaRequest $request, Siswa $siswa)
    {
        $this->authorizeJenjang($siswa);
        $data = $request->validated();

        if ($data['jenjang'] !== 'TK') {
            $data['nominal_mamin'] = 0;
        }
        if ($data['status'] === 'tidak_aktif' && !$siswa->tanggal_keluar) {
            $data['tanggal_keluar'] = now()->toDateString();
        }
        if ($data['status'] === 'aktif') {
            $data['tanggal_keluar'] = null;
        }

        $siswa->update($data);

        return redirect()->route('siswa.show', $siswa)
                         ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $this->authorizeJenjang($siswa);
        $nama = $siswa->nama;
        $siswa->delete();

        return redirect()->route('siswa.index')
                         ->with('success', 'Data siswa "' . $nama . '" berhasil dihapus.');
    }

    // ─── Private Helpers ────────────────────────────────────────────

    private function authorizeJenjang(Siswa $siswa): void
    {
        $user = Auth::user();
        if ($user->jenjang && $user->jenjang !== $siswa->jenjang) {
            abort(403, 'Anda tidak memiliki akses ke data siswa ini.');
        }
    }

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

    private function hitungStatusBulan(array $bulanAktif, array $bulanDibayar, $pembayaranCollection): array
    {
        $namaBulan = [
            '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
        ];

        $status = [];
        foreach ($bulanAktif as $bulan) {
            [, $bln] = explode('-', $bulan);
            $sudahBayar = in_array($bulan, $bulanDibayar, true);
            $dataBayar  = null;

            foreach ($pembayaranCollection as $p) {
                $raw    = $p->getRawOriginal('bulan_bayar');
                $bBayar = Siswa::safeDecode($raw);
                if (in_array($bulan, $bBayar, true)) {
                    $dataBayar  = $p;
                    $sudahBayar = true;
                    break;
                }
            }

            $status[] = [
                'bulan'       => $bulan,
                'nama_bulan'  => $namaBulan[$bln] ?? $bln,
                'sudah_bayar' => $sudahBayar,
                'data_bayar'  => $dataBayar,
            ];
        }

        return $status;
    }
}