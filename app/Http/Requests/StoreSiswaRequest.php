<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            // ── Informasi Siswa ───────────────────────────────────────────
            'id_siswa'           => ['nullable', 'string', 'max:20'],
            'nama'               => ['required', 'string', 'max:255'],
            'jenjang'            => ['required', 'in:TK,SD,SMP'],
            'status'             => ['required', 'in:aktif,tidak_aktif'],
            'tanggal_masuk'      => ['required', 'date'],
            'tanggal_keluar'     => ['nullable', 'date', 'after_or_equal:tanggal_masuk'],
            'keterangan'         => ['nullable', 'string', 'max:255'],
            'no_hp_wali'         => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],

            // ── SPP & Kelas (siswa_kelas) — opsional saat create ─────────
            'tahun_pelajaran_id' => ['nullable', 'exists:tahun_pelajaran,id'],
            'kelas_id'           => ['nullable', 'exists:kelas,id'],
            'nominal_spp'        => ['nullable', 'numeric', 'min:0'],
            'nominal_donator'    => ['nullable', 'numeric', 'min:0'],
            'nominal_mamin'      => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'              => 'Nama siswa wajib diisi.',
            'nama.max'                   => 'Nama maksimal 255 karakter.',
            'jenjang.required'           => 'Jenjang wajib dipilih.',
            'jenjang.in'                 => 'Jenjang harus TK, SD, atau SMP.',
            'status.required'            => 'Status wajib dipilih.',
            'status.in'                  => 'Status tidak valid.',
            'tanggal_masuk.required'     => 'Tanggal masuk wajib diisi.',
            'tanggal_masuk.date'         => 'Format tanggal masuk tidak valid.',
            'tanggal_keluar.date'        => 'Format tanggal keluar tidak valid.',
            'tanggal_keluar.after_or_equal' => 'Tanggal keluar tidak boleh sebelum tanggal masuk.',
            'tahun_pelajaran_id.exists'  => 'Tahun pelajaran tidak ditemukan.',
            'kelas_id.exists'            => 'Kelas tidak ditemukan.',
            'nominal_spp.numeric'        => 'Nominal SPP harus berupa angka.',
            'nominal_spp.min'            => 'Nominal SPP tidak boleh negatif.',
            'nominal_donator.numeric'    => 'Nominal donatur harus berupa angka.',
            'nominal_donator.min'        => 'Nominal donatur tidak boleh negatif.',
            'nominal_mamin.numeric'      => 'Nominal mamin harus berupa angka.',
            'nominal_mamin.min'          => 'Nominal mamin tidak boleh negatif.',
        ];
    }
}