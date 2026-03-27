<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $userJenjang = Auth::user()->jenjang; // null = admin yayasan

        if ($userJenjang) {
            // Admin jenjang: hanya lihat/edit jenjangnya sendiri
            $setting = Setting::forJenjang($userJenjang);
            return view('setting.index', compact('setting', 'userJenjang'));
        }

        // Admin yayasan: lihat semua jenjang (TK, SD, SMP)
        $settings    = Setting::allIndexed(); // ['TK'=>..., 'SD'=>..., 'SMP'=>...]
        $userJenjang = null;
        return view('setting.index', compact('settings', 'userJenjang'));
    }

    public function update(Request $request)
    {
        $userJenjang = Auth::user()->jenjang;

        // ── Validasi ─────────────────────────────────────────────────
        $rules = [
            'jenjang'             => 'required|in:TK,SD,SMP',
            'nama_sekolah'        => 'nullable|string|max:150',
            'nama_yayasan'        => 'nullable|string|max:150',
            'nama_kepala_sekolah' => 'nullable|string|max:100',
            'nip_kepala_sekolah'  => 'nullable|string|max:30',
            'nama_admin'          => 'nullable|string|max:100',
            'alamat'              => 'nullable|string|max:255',
            'kota'                => 'nullable|string|max:80',
            'telepon'             => 'nullable|string|max:20',
            'logo'                => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'tanda_tangan'        => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ];

        $messages = [
            'logo.image'          => 'Logo harus berupa gambar.',
            'logo.mimes'          => 'Logo harus format PNG atau JPG.',
            'logo.max'            => 'Logo maksimal 2 MB.',
            'tanda_tangan.image'  => 'Tanda tangan harus berupa gambar.',
            'tanda_tangan.mimes'  => 'Tanda tangan harus format PNG atau JPG.',
            'tanda_tangan.max'    => 'Tanda tangan maksimal 2 MB.',
        ];

        $request->validate($rules, $messages);

        $targetJenjang = $request->jenjang;

        // Keamanan: admin jenjang hanya boleh update miliknya sendiri
        if ($userJenjang && $targetJenjang !== $userJenjang) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah data sekolah jenjang lain.');
        }

        // ── Ambil / buat record ──────────────────────────────────────
        $setting = Setting::firstOrCreate(['jenjang' => $targetJenjang]);

        // Semua jenjang menyimpan data lengkapnya sendiri
        $data = $request->only([
            'nama_sekolah',
            'nama_yayasan',
            'nama_kepala_sekolah',
            'nip_kepala_sekolah',
            'nama_admin',
            'alamat',
            'kota',
            'telepon',
        ]);

        // ── Logo ──────────────────────────────────────────────────────
        if ($request->hasFile('logo')) {
            if ($setting->logo) Storage::disk('public')->delete($setting->logo);
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }
        if ($request->boolean('hapus_logo') && $setting->logo) {
            Storage::disk('public')->delete($setting->logo);
            $data['logo'] = null;
        }

        // ── Tanda Tangan ──────────────────────────────────────────────
        if ($request->hasFile('tanda_tangan')) {
            if ($setting->tanda_tangan) Storage::disk('public')->delete($setting->tanda_tangan);
            $data['tanda_tangan'] = $request->file('tanda_tangan')->store('settings', 'public');
        }
        if ($request->boolean('hapus_tanda_tangan') && $setting->tanda_tangan) {
            Storage::disk('public')->delete($setting->tanda_tangan);
            $data['tanda_tangan'] = null;
        }

        $setting->update($data);

        return redirect()
            ->route('setting.index', ['tab' => $targetJenjang])
            ->with('success', "Data Sekolah {$targetJenjang} berhasil disimpan.");
    }
}