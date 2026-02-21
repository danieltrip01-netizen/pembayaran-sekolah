<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $siswaId = $this->route('siswa');

        return [
            'nama'               => 'required|string|max:100',
            'kelas'              => 'required|string|max:10',
            'jenjang'            => 'required|in:TK,SD,SMP',
            'nominal_pembayaran' => 'required|numeric|min:0',
            'nominal_donator'    => 'required|numeric|min:0',
            'nominal_mamin'      => 'nullable|numeric|min:0',
            'tanggal_masuk'      => 'required|date',
            'tanggal_keluar'     => 'nullable|date|after:tanggal_masuk',
            'status'             => 'required|in:aktif,tidak_aktif',
            'keterangan'         => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'               => 'Nama siswa wajib diisi.',
            'kelas.required'              => 'Kelas wajib diisi.',
            'jenjang.required'            => 'Jenjang wajib dipilih.',
            'nominal_pembayaran.required' => 'Nominal pembayaran wajib diisi.',
            'tanggal_masuk.required'      => 'Tanggal masuk wajib diisi.',
        ];
    }
}