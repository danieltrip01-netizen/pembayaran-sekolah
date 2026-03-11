<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Setoran;
use App\Models\Setting;
use App\Models\TahunPelajaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetoranController extends Controller
{
    // ─── Index ───────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $jenjang        = Auth::user()->jenjang;
        $tahunPelajaran = TahunPelajaran::aktif();

        $query = Setoran::withCount('pembayaran')
            ->with('user')
            // Filter hanya setoran milik tahun pelajaran aktif
            ->when(
                $tahunPelajaran,
                fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaran->id),
                fn($q) => $q->whereRaw('0 = 1')
            )
            ->when($jenjang, fn($q) => $q->where('jenjang', $jenjang))
            ->when(
                $request->filled('tanggal_dari'),
                fn($q) => $q->whereDate('tanggal_setoran', '>=', $request->tanggal_dari)
            )
            ->when(
                $request->filled('tanggal_sampai'),
                fn($q) => $q->whereDate('tanggal_setoran', '<=', $request->tanggal_sampai)
            )
            ->when(
                $request->filled('jenjang') && !$jenjang,
                fn($q) => $q->where('jenjang', $request->jenjang)
            )
            ->when(
                $request->filled('search'),
                fn($q) => $q->where('kode_setoran', 'like', '%' . $request->search . '%')
            );

        // Hitung total SEBELUM paginate (seluruh hasil filter, bukan hanya halaman ini)
        $grandTotalAll   = (clone $query)->sum('total_keseluruhan');
        $totalNominalAll = (clone $query)->sum('total_nominal');
        $totalMaminAll   = (clone $query)->sum('total_mamin');

        $setoran = $query->latest('tanggal_setoran')
            ->paginate(15)
            ->withQueryString();

        return view('setoran.index', compact(
            'setoran',
            'grandTotalAll',
            'totalNominalAll',
            'totalMaminAll',
            'tahunPelajaran',
        ));
    }

    // ─── Create ──────────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $userJenjang    = Auth::user()->jenjang;
        $tahunPelajaran = TahunPelajaran::aktif();

        if ($userJenjang) {
            $jenjang      = $userJenjang;
            $pilihJenjang = false;
        } else {
            $jenjang      = $request->filled('jenjang') ? $request->jenjang : null;
            $pilihJenjang = true;
        }

        $pembayaranBelumSetor = collect();

        if ($jenjang) {
            $pembayaranBelumSetor = Pembayaran::with([
                    'siswa',
                    'pembayaranBulan',        // kolom Bulan — menggantikan bulan_bayar JSON
                    'siswaKelas.kelas',        // kolom Kelas — menggantikan $siswa->kelas
                ])
                ->whereNull('setoran_id')
                // Hanya pembayaran milik tahun pelajaran aktif
                ->when(
                    $tahunPelajaran,
                    fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaran->id),
                    fn($q) => $q->whereRaw('0 = 1')
                )
                ->whereHas('siswa', fn($q) => $q->where('jenjang', $jenjang))
                ->orderBy('tanggal_bayar')
                ->get();
        }

        return view('setoran.create', compact(
            'pembayaranBelumSetor',
            'jenjang',
            'pilihJenjang',
            'tahunPelajaran',
        ));
    }

    // ─── Store ───────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_setoran'  => ['required', 'date'],
            'jenjang'          => ['required', 'in:TK,SD,SMP'],
            'pembayaran_ids'   => ['required', 'array', 'min:1'],
            'pembayaran_ids.*' => ['exists:pembayaran,id'],
            'keterangan'       => ['nullable', 'string', 'max:255'],
        ], [
            'pembayaran_ids.required' => 'Pilih minimal 1 pembayaran untuk disetorkan.',
            'pembayaran_ids.min'      => 'Pilih minimal 1 pembayaran untuk disetorkan.',
        ]);

        $tahunPelajaran = TahunPelajaran::aktif();

        // Ambil hanya pembayaran yang:
        //   1. Id-nya ada di request
        //   2. Belum disetor (setoran_id null)
        //   3. Milik tahun pelajaran aktif
        // → mencegah manipulasi id dari luar
        $pembayaranList = Pembayaran::whereIn('id', $validated['pembayaran_ids'])
            ->whereNull('setoran_id')
            ->when(
                $tahunPelajaran,
                fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaran->id)
            )
            ->get();

        if ($pembayaranList->isEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Tidak ada pembayaran valid yang bisa disetorkan.');
        }

        // total_nominal      = SPP bersih (total_bayar dikurangi mamin)
        // total_mamin        = total mamin semua transaksi
        // total_keseluruhan  = total uang diterima dari orang tua = sum(total_bayar)
        $totalMamin       = $pembayaranList->sum('nominal_mamin');
        $totalNominal     = $pembayaranList->sum(
            fn($p) => (float) $p->total_bayar - (float) $p->nominal_mamin
        );
        $totalKeseluruhan = $pembayaranList->sum('total_bayar');

        $setoran = DB::transaction(function () use ($validated, $tahunPelajaran, $pembayaranList, $totalNominal, $totalMamin, $totalKeseluruhan) {
            $setoran = Setoran::create([
                'kode_setoran'       => Setoran::generateKodeSetoran($validated['jenjang']),
                'tanggal_setoran'    => $validated['tanggal_setoran'],
                'jenjang'            => $validated['jenjang'],
                'tahun_pelajaran_id' => $tahunPelajaran?->id,
                'total_nominal'      => $totalNominal,
                'total_mamin'        => $totalMamin,
                'total_keseluruhan'  => $totalKeseluruhan,
                'user_id'            => Auth::id(),
                'keterangan'         => $validated['keterangan'] ?? null,
            ]);

            Pembayaran::whereIn('id', $pembayaranList->pluck('id'))
                ->update(['setoran_id' => $setoran->id]);

            return $setoran;
        });

        return redirect()->route('setoran.show', $setoran)
            ->with('success', 'Setoran ' . $setoran->kode_setoran . ' berhasil disimpan.');
    }

    // ─── Show ────────────────────────────────────────────────────────────────

    public function show(Setoran $setoran)
    {
        $setoran->load([
            'user',
            'tahunPelajaran',
            'pembayaran.siswa',
            'pembayaran.pembayaranBulan',    // ganti $p->bulan_bayar JSON
            'pembayaran.siswaKelas.kelas',   // ganti $p->siswa->kelas
        ]);

        return view('setoran.show', compact('setoran'));
    }

    // ─── Cetak PDF ───────────────────────────────────────────────────────────

    public function cetak(Setoran $setoran)
    {
        $setoran->load([
            'user',
            'tahunPelajaran',
            'pembayaran.siswa',
            'pembayaran.pembayaranBulan',
            'pembayaran.siswaKelas.kelas',
        ]);

        $globalSetting  = Setting::global();
        $jenjangSetting = Setting::forJenjang($setoran->jenjang);

        $pdf = Pdf::loadView('cetak.setoran', compact(
            'setoran',
            'globalSetting',
            'jenjangSetting',
        ))->setPaper('a5', 'portrait');

        return $pdf->stream('setoran-' . $setoran->kode_setoran . '.pdf');
    }

    // ─── Destroy ─────────────────────────────────────────────────────────────

    public function destroy(Setoran $setoran): RedirectResponse
    {
        $kode = $setoran->kode_setoran;

        // Lepas relasi pembayaran → dapat disetor ulang
        $setoran->pembayaran()->update(['setoran_id' => null]);
        $setoran->delete();

        return redirect()->route('setoran.index')
            ->with('success', 'Setoran ' . $kode . ' berhasil dihapus. Pembayaran terkait dapat disetor ulang.');
    }
}