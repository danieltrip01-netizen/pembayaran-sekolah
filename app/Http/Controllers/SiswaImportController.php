<?php

namespace App\Http\Controllers;

use App\Imports\SiswaImport;
use App\Exports\SiswaTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class SiswaImportController extends Controller
{
    /**
     * Menampilkan halaman form import.
     */
    public function index()
    {
        $jenjang = Auth::user()->jenjang;
        return view('siswa.import', compact('jenjang'));
    }

    /**
     * Memproses file Excel yang diunggah.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes'    => 'Format file harus .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        $userJenjang = Auth::user()->jenjang;

        try {
            $importer = new SiswaImport($userJenjang);
            Excel::import($importer, $request->file('file'));

            $imported = $importer->getCountImported();
            $skipped  = $importer->getCountSkipped();
            $failures = $importer->getErrorRows();

            // Susun pesan ringkasan
            $parts = [];
            if ($imported > 0) $parts[] = "<strong>{$imported}</strong> siswa berhasil ditambahkan";
            if ($skipped > 0)  $parts[] = "<strong>{$skipped}</strong> baris dilewati/gagal";

            $summary = $parts
                ? 'Proses selesai: ' . implode(', ', $parts) . '.'
                : 'Tidak ada data yang diproses. Periksa kembali isi file Anda.';

            // ✅ Gunakan flash() agar notifikasi otomatis hilang setelah satu kali tampil
            session()->flash('import_summary',  $summary);
            session()->flash('import_failures', $failures ?? []);
            session()->flash('import_ok',       $imported);

            return redirect()->route('siswa.import.index');

        } catch (ValidationException $e) {
            $validationFailures = $e->failures();
            $errorList = array_map(fn($f) => [
                'row'   => $f->row(),
                'nama'  => $f->values()['nama'] ?? 'Tidak diketahui',
                'pesan' => implode(', ', $f->errors()),
            ], $validationFailures);

            session()->flash('import_summary',  'Import gagal: Terdapat ' . count($validationFailures) . ' baris dengan data tidak valid.');
            session()->flash('import_failures', $errorList);
            session()->flash('import_ok',       0);

            return redirect()->route('siswa.import.index');

        } catch (\Exception $e) {
            return redirect()->route('siswa.import.index')
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Mengunduh template Excel.
     */
    public function downloadTemplate()
    {
        return Excel::download(
            new SiswaTemplateExport(),
            'template-import-siswa.xlsx'
        );
    }
}