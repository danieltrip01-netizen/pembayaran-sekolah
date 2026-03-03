<?php

namespace App\Http\Controllers;

use App\Models\KreditLog;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KreditController extends Controller
{
    /**
     * Riwayat kredit semua siswa.
     * GET /kredit
     */
    public function index(Request $request)
    {
        $jenjang = Auth::user()->jenjang;

        $query = KreditLog::with('siswa', 'user', 'pembayaran')
            ->latest();

        if ($jenjang) {
            $query->whereHas('siswa', fn($q) => $q->where('jenjang', $jenjang));
        }
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->filled('search')) {
            $query->whereHas('siswa', fn($q) =>
                $q->where('nama', 'like', '%' . $request->search . '%'));
        }
        if ($request->filled('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }

        $log = $query->paginate(20)->withQueryString();

        return view('kredit.index', compact('log'));
    }

    /**
     * Form tambah kredit untuk satu siswa.
     * GET /kredit/{siswa}/create
     */
    public function create(Siswa $siswa)
    {
        // Pastikan user hanya boleh akses siswa sesuai jenjangnya
        $userJenjang = Auth::user()->jenjang;
        if ($userJenjang && $siswa->jenjang !== $userJenjang) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $log = $siswa->kreditLog()
            ->with('user', 'pembayaran')
            ->latest()
            ->paginate(15);

        return view('kredit.create', compact('siswa', 'log'));
    }

    /**
     * Simpan kredit baru.
     * POST /kredit/{siswa}
     */
    public function store(Request $request, Siswa $siswa)
    {
        // Pastikan user hanya boleh akses siswa sesuai jenjangnya
        $userJenjang = Auth::user()->jenjang;
        if ($userJenjang && $siswa->jenjang !== $userJenjang) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $data = $request->validate([
            'jumlah'     => ['required', 'integer', 'min:1000'],
            'keterangan' => ['required', 'string', 'max:255'],
        ], [
            'jumlah.required'     => 'Jumlah kredit wajib diisi.',
            'jumlah.min'          => 'Jumlah kredit minimal Rp 1.000.',
            'keterangan.required' => 'Keterangan wajib diisi.',
        ]);

        $siswa->tambahKredit(
            jumlah     : (int) $data['jumlah'],
            keterangan : $data['keterangan'],
        );

        return redirect()->route('kredit.create', $siswa)
            ->with('success',
                'Kredit Rp ' . number_format($data['jumlah'], 0, ',', '.') .
                ' berhasil ditambahkan untuk ' . e($siswa->nama) . '.'
            );
    }
}