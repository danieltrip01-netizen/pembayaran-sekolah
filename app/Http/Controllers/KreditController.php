<?php

namespace App\Http\Controllers;

use App\Models\KreditLog;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    /**
     * Batalkan entri kredit 'tambah' — diperlukan saat nilai donatur
     * dikoreksi kembali ke semula sehingga kredit tidak otomatis hilang.
     * DELETE /kredit/{log}
     */
    public function destroy(KreditLog $log)
    {
        abort_if($log->tipe !== 'tambah', 403, 'Hanya kredit tipe tambah yang dapat dibatalkan.');
        abort_if($log->trashed(), 409, 'Kredit ini sudah dibatalkan.');

        $siswa = $log->siswa;

        $userJenjang = Auth::user()->jenjang;
        if ($userJenjang && $siswa->jenjang !== $userJenjang) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // Cegah saldo negatif
        if ($siswa->saldo_kredit < $log->jumlah) {
            return back()->with('error',
                'Tidak dapat dibatalkan: saldo kredit saat ini (Rp ' .
                number_format($siswa->saldo_kredit, 0, ',', '.') .
                ') lebih kecil dari jumlah kredit yang akan dibatalkan (Rp ' .
                number_format($log->jumlah, 0, ',', '.') . ').'
            );
        }

        DB::transaction(function () use ($log, $siswa) {
            $siswa->decrement('saldo_kredit', $log->jumlah);

            // Catat jejak audit pembatalan sebagai entri 'pakai'
            KreditLog::create([
                'siswa_id'           => $siswa->id,
                'user_id'            => Auth::id(),
                'tipe'               => 'pakai',
                'jumlah'             => $log->jumlah,
                'saldo_sebelum'      => $siswa->getOriginal('saldo_kredit') + $log->jumlah,
                'saldo_sesudah'      => $siswa->fresh()->saldo_kredit,
                'keterangan'         => 'Pembatalan kredit: ' . $log->keterangan,
                'dibatalkan_oleh_id' => Auth::id(),
            ]);

            // Soft-delete baris asli agar tetap tampil di riwayat sebagai "Dibatalkan"
            $log->update(['dibatalkan_oleh_id' => Auth::id()]);
            $log->delete();
        });

        return redirect()->route('kredit.create', $siswa)
            ->with('success',
                'Kredit Rp ' . number_format($log->jumlah, 0, ',', '.') .
                ' berhasil dibatalkan.'
            );
    }
}