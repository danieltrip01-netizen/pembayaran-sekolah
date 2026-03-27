<?php

namespace App\Http\Controllers;

use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $jenjang = Auth::user()->jenjang; // null = admin yayasan (semua jenjang)

        $daftarTahun = TahunPelajaran::withCount([
            // Hitung siswa sesuai jenjang user. Jika null (admin yayasan), hitung semua.
            'siswaKelas' => fn($q) => $jenjang
                ? $q->whereHas('siswa', fn($s) => $s->where('jenjang', $jenjang))
                : $q,
            // Filter pembayaran via siswa.jenjang (siswa_id selalu ada, siswaKelas nullable)
            'pembayaran' => fn($q) => $jenjang
                ? $q->whereHas('siswa', fn($s) => $s->where('jenjang', $jenjang))
                : $q,
            // Setoran punya kolom jenjang sendiri — filter langsung
            'setoran' => fn($q) => $jenjang
                ? $q->where('jenjang', $jenjang)
                : $q,
        ])
        ->orderByDesc('tanggal_mulai')
        ->get();

        return view('tahun-pelajaran.index', compact('daftarTahun', 'jenjang'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'            => ['required', 'string', 'max:50',
                                  Rule::unique(TahunPelajaran::class, 'nama')],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
        ], [
            'nama.unique'           => 'Tahun pelajaran dengan nama ini sudah ada.',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        $data['is_active'] = false;
        $data['is_locked'] = false;

        TahunPelajaran::create($data);

        return redirect()->route('tahun-pelajaran.index')
            ->with('success', 'Tahun pelajaran "' . $data['nama'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, TahunPelajaran $tahunPelajaran)
    {
        $data = $request->validate([
            'nama'            => ['required', 'string', 'max:50',
                                  Rule::unique(TahunPelajaran::class, 'nama')
                                      ->ignore($tahunPelajaran->id)],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
        ], [
            'nama.unique'           => 'Tahun pelajaran dengan nama ini sudah ada.',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        if ($tahunPelajaran->is_locked) {
            return back()->with('error', 'Tahun pelajaran yang sudah dikunci tidak dapat diedit.');
        }

        $tahunPelajaran->update($data);

        return redirect()->route('tahun-pelajaran.index')
            ->with('success', 'Tahun pelajaran "' . $tahunPelajaran->nama . '" berhasil diperbarui.');
    }

    public function destroy(TahunPelajaran $tahunPelajaran)
    {
        if ($tahunPelajaran->is_locked) {
            return back()->with('error', 'Tahun pelajaran yang dikunci tidak dapat dihapus.');
        }
        if ($tahunPelajaran->is_active) {
            return back()->with('error', 'Non-aktifkan tahun pelajaran sebelum menghapus.');
        }

        $adaData = $tahunPelajaran->siswaKelas()->exists()
                || $tahunPelajaran->pembayaran()->exists()
                || $tahunPelajaran->setoran()->exists();

        if ($adaData) {
            return back()->with('error',
                'Tahun pelajaran tidak dapat dihapus karena sudah memiliki data terkait.'
            );
        }

        $nama = $tahunPelajaran->nama;
        $tahunPelajaran->delete();

        return redirect()->route('tahun-pelajaran.index')
            ->with('success', 'Tahun pelajaran "' . $nama . '" berhasil dihapus.');
    }

    /**
     * Aktifkan satu tahun pelajaran.
     * Model boot() sudah menangani deactivate-others secara otomatis.
     */
    public function activate(TahunPelajaran $tahunPelajaran)
    {
        $tahunPelajaran->update(['is_active' => true]);

        return redirect()->route('tahun-pelajaran.index')
            ->with('success', '"' . $tahunPelajaran->nama . '" kini menjadi tahun pelajaran aktif.');
    }

    /**
     * Toggle kunci tahun pelajaran.
     * Mengunci: hanya boleh jika tahun sedang aktif.
     * Membuka kunci: selalu boleh.
     */
    public function toggleLock(TahunPelajaran $tahunPelajaran)
    {
        // Hanya cegah penguncian (bukan pembukaan) jika tahun tidak aktif
        if (!$tahunPelajaran->is_locked && !$tahunPelajaran->is_active) {
            return back()->with('error', 'Hanya tahun pelajaran aktif yang dapat dikunci.');
        }

        $tahunPelajaran->update(['is_locked' => !$tahunPelajaran->is_locked]);

        $pesan = $tahunPelajaran->is_locked
            ? '"' . $tahunPelajaran->nama . '" berhasil dikunci. Data tidak dapat diubah.'
            : '"' . $tahunPelajaran->nama . '" berhasil dibuka kuncinya.';

        return redirect()->route('tahun-pelajaran.index')->with('success', $pesan);
    }
}