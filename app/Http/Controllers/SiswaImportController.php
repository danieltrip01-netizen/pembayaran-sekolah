<?php

namespace App\Http\Controllers;

use App\Exports\SiswaExport;
use App\Imports\SiswaImport;
use App\Exports\SiswaTemplateExport;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class SiswaImportController extends Controller
{
    /**
     * Halaman form import.
     */
    public function index()
    {
        $jenjang        = Auth::user()->jenjang;
        $tahunPelajaran = TahunPelajaran::aktif();

        // Tahun sebelumnya (untuk ditampilkan sebagai info di blade)
        $tahunSebelumnya = $this->getTahunSebelumnya($tahunPelajaran);

        return view('siswa.import', compact('jenjang', 'tahunPelajaran', 'tahunSebelumnya'));
    }

    /**
     * Proses file Excel yang diunggah.
     * Mendukung dua mode via field tersembunyi `import_mode`:
     *   - 'new'    → tambah siswa baru
     *   - 'update' → update siswa_kelas tahun pelajaran aktif berdasarkan id_siswa
     */
    public function import(Request $request)
    {
        $request->validate([
            'file'        => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'import_mode' => ['required', 'in:new,update'],
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes'    => 'Format file harus .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        $userJenjang = Auth::user()->jenjang;
        $mode        = $request->input('import_mode', 'new');

        try {
            $importer = new SiswaImport($userJenjang, $mode);
            Excel::import($importer, $request->file('file'));

            $imported = $importer->getCountImported();
            $skipped  = $importer->getCountSkipped();
            $failures = $importer->getErrorRows();

            $modeLabel = $mode === 'update' ? 'diperbarui' : 'ditambahkan';

            $parts = [];
            if ($imported > 0) $parts[] = "<strong>{$imported}</strong> siswa berhasil {$modeLabel}";
            if ($skipped > 0)  $parts[] = "<strong>{$skipped}</strong> baris dilewati/gagal";

            $summary = $parts
                ? 'Proses selesai: ' . implode(', ', $parts) . '.'
                : 'Tidak ada data yang diproses. Periksa kembali isi file Anda.';

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

        } catch (\RuntimeException $e) {
            // Misal: tidak ada tahun pelajaran aktif
            return redirect()->route('siswa.import.index')
                ->with('error', $e->getMessage());

        } catch (\Exception $e) {
            return redirect()->route('siswa.import.index')
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Export data siswa ke Excel.
     *
     * Parameter URL opsional:
     *   ?sumber=aktif      → ambil data dari tahun pelajaran aktif (default)
     *   ?sumber=sebelumnya → ambil data dari tahun pelajaran sebelum yang aktif
     *
     * File ini bisa diedit (nominal, kelas) lalu di-reimport dengan mode 'update'.
     */
    public function export(Request $request)
    {
        $tahunAktif = TahunPelajaran::aktif();

        if (!$tahunAktif) {
            return redirect()->route('siswa.import.index')
                ->with('error', 'Tidak ada tahun pelajaran aktif. Aktifkan tahun pelajaran terlebih dahulu.');
        }

        // Tentukan tahun sumber data berdasarkan parameter ?sumber=
        $sumber = $request->input('sumber', 'aktif');

        if ($sumber === 'sebelumnya') {
            $tahunSumber = $this->getTahunSebelumnya($tahunAktif);

            if (!$tahunSumber) {
                return redirect()->route('siswa.import.index')
                    ->with('error', 'Tidak ditemukan tahun pelajaran sebelumnya untuk dijadikan sumber ekspor.');
            }
        } else {
            $tahunSumber = $tahunAktif;
        }

        $jenjang  = Auth::user()->jenjang;

        // Saat ekspor dari tahun sebelumnya (untuk naik kelas),
        // siswa tingkat akhir (VI/IX/OB) otomatis dikecualikan karena dianggap lulus.
        $filterKelasAkhir = ($sumber === 'sebelumnya');

        $exporter = new SiswaExport($jenjang, $tahunSumber, $filterKelasAkhir);

        $namaFile = 'salin-data-' . str_replace('/', '-', $tahunSumber->nama)
                  . ($jenjang ? '-' . strtolower($jenjang) : '')
                  . ($sumber === 'sebelumnya' ? '' : '')
                  . '.xlsx';

        return Excel::download($exporter, $namaFile);
    }

    /**
     * Download template Excel kosong untuk import siswa baru.
     */
    public function downloadTemplate()
    {
        return Excel::download(
            new SiswaTemplateExport(),
            'template-import-siswa.xlsx'
        );
    }

    /**
     * Ambil tahun pelajaran tepat sebelum tahun yang diberikan.
     * Urutan didasarkan pada kolom `urutan` (atau `id` jika tidak ada).
     */
    private function getTahunSebelumnya(?TahunPelajaran $tahunAktif): ?TahunPelajaran
    {
        if (!$tahunAktif) {
            return null;
        }

        // Coba berdasarkan kolom `urutan` jika ada, fallback ke `id`
        $kolom = \Schema::hasColumn('tahun_pelajarans', 'urutan') ? 'urutan' : 'id';

        return TahunPelajaran::where($kolom, '<', $tahunAktif->{$kolom})
            ->orderByDesc($kolom)
            ->first();
    }
}