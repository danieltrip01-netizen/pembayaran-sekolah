<?php

namespace App\Http\Controllers;

use App\Models\Setoran;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class SetoranController extends Controller
{
    public function index(Request $request)
    {
        $user    = Auth::user();
        $jenjang = $user->jenjang;

        $query = Setoran::withCount('pembayaran')
            ->with('user')
            ->when($jenjang, fn($q) => $q->where('jenjang', $jenjang));

        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_setoran', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_setoran', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('jenjang') && !$jenjang) {
            $query->where('jenjang', $request->jenjang);
        }
        if ($request->filled('search')) {
            $query->where('kode_setoran', 'like', '%' . $request->search . '%');
        }

        // ✅ Hitung total keseluruhan (semua data, bukan hanya halaman ini)
        // Clone query sebelum paginate untuk menghitung grand total
        $queryTotal     = clone $query;
        $grandTotalAll  = $queryTotal->sum('total_keseluruhan');
        $totalNominalAll = (clone $query)->sum('total_nominal');
        $totalMaminAll  = (clone $query)->sum('total_mamin');

        $setoran = $query->latest('tanggal_setoran')
            ->paginate(15)
            ->withQueryString();

        return view('setoran.index', compact(
            'setoran',
            'grandTotalAll',
            'totalNominalAll',
            'totalMaminAll'
        ));
    }

    public function create(Request $request)
    {
        $user        = Auth::user();
        $userJenjang = $user->jenjang; // null = admin yayasan, ada isi = admin jenjang

        if ($userJenjang) {
            // Admin TK/SD/SMP: langsung pakai jenjangnya sendiri
            $jenjang      = $userJenjang;
            $pilihJenjang = false;
        } else {
            // Admin yayasan: ambil dari ?jenjang=xx, null = belum pilih
            $jenjang      = $request->filled('jenjang') ? $request->jenjang : null;
            $pilihJenjang = true;
        }

        $pembayaranBelumSetor = collect();
        if ($jenjang) {
            $pembayaranBelumSetor = Pembayaran::with('siswa')
                ->whereNull('setoran_id')
                ->whereHas('siswa', fn($q) => $q->where('jenjang', $jenjang))
                ->orderBy('tanggal_bayar')
                ->get();
        }

        return view('setoran.create', compact(
            'pembayaranBelumSetor',
            'jenjang',
            'pilihJenjang'      // ✅ selalu dikirim ke view
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_setoran' => 'required|date',
            'jenjang'         => 'required|in:TK,SD,SMP',
            'pembayaran_ids'  => 'required|array|min:1',
            'pembayaran_ids.*' => 'exists:pembayaran,id',
            'keterangan'      => 'nullable|string|max:255',
        ]);

        $pembayaranList = Pembayaran::whereIn('id', $request->pembayaran_ids)->get();

        // ✅ FIX: nominal_donator & nominal_mamin di DB sudah tersimpan sebagai TOTAL (× jumlah_bulan).
        //   total_nominal  = SPP bersih (sudah dikurangi donatur, belum termasuk mamin)
        //   total_mamin    = total mamin semua transaksi
        //   total_keseluruhan = total uang yang diterima dari orang tua = sum(total_bayar)
        $totalMamin       = $pembayaranList->sum('nominal_mamin');
        $totalNominal     = $pembayaranList->sum(fn($p) => (float)$p->total_bayar - (float)$p->nominal_mamin);
        $totalKeseluruhan = $pembayaranList->sum('total_bayar');

        $setoran = Setoran::create([
            'kode_setoran'      => Setoran::generateKodeSetoran($request->jenjang),
            'tanggal_setoran'   => $request->tanggal_setoran,
            'jenjang'           => $request->jenjang,
            'total_nominal'     => $totalNominal,
            'total_mamin'       => $totalMamin,
            'total_keseluruhan' => $totalKeseluruhan,
            'user_id'           => auth()->id(),
            'keterangan'        => $request->keterangan,
        ]);

        // Update pembayaran dengan setoran_id
        Pembayaran::whereIn('id', $request->pembayaran_ids)
            ->update(['setoran_id' => $setoran->id]);

        return redirect()->route('setoran.show', $setoran)
            ->with('success', 'Setoran berhasil disimpan.');
    }

    public function show(Setoran $setoran)
    {
        $setoran->load(['user', 'pembayaran.siswa']);
        return view('setoran.show', compact('setoran'));
    }

    public function cetak(Setoran $setoran)
    {
        $setoran->load(['user', 'pembayaran.siswa']);

        $pdf = Pdf::loadView('cetak.setoran', compact('setoran'))
            ->setPaper('a5', 'portrait');

        return $pdf->stream('setoran-' . $setoran->kode_setoran . '.pdf');
    }

    public function destroy(Setoran $setoran)
    {
        // Lepas relasi pembayaran dari setoran ini
        $setoran->pembayaran()->update(['setoran_id' => null]);
        $setoran->delete();

        return redirect()->route('setoran.index')
            ->with('success', 'Setoran berhasil dihapus.');
    }
}