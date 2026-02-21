<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePembayaranRequest extends FormRequest
{
    // ✅ FIX: Ganti auth()->check() dengan Auth::check()
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'siswa_id'        => 'required|exists:siswa,id',
            'tanggal_bayar'   => 'required|date',
            'bulan_bayar'     => 'required|array|min:1',
            'bulan_bayar.*'   => 'required|string|regex:/^\d{4}-\d{2}$/',
            'nominal_donator' => 'nullable|numeric|min:0',
            'keterangan'      => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'siswa_id.required'      => 'Siswa wajib dipilih.',
            'siswa_id.exists'        => 'Siswa tidak ditemukan.',
            'tanggal_bayar.required' => 'Tanggal bayar wajib diisi.',
            'bulan_bayar.required'   => 'Pilih minimal satu bulan pembayaran.',
            'bulan_bayar.min'        => 'Pilih minimal satu bulan pembayaran.',
            'bulan_bayar.*.regex'    => 'Format bulan tidak valid.',
        ];
    }
}