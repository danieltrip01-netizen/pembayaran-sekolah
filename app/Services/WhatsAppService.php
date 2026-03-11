<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token;
    private string $apiUrl = 'https://api.fonnte.com/send';

    public function __construct()
    {
        $this->token = config('services.fonnte.token', '');
    }

    /**
     * Kirim pesan WA ke satu nomor.
     *
     * @param  string $nomor   Format: 08xxx atau 628xxx
     * @param  string $pesan   Teks pesan (mendukung *bold* dan _italic_)
     * @return bool
     */
    public function kirim(string $nomor, string $pesan): bool
    {
        if (empty($this->token)) {
            Log::warning('WhatsApp: token Fonnte belum dikonfigurasi.');
            return false;
        }

        // Normalkan nomor: 08xxx → 628xxx
        $nomor = $this->normalizeNomor($nomor);
        if (!$nomor) {
            Log::warning('WhatsApp: nomor tidak valid.', ['nomor' => $nomor]);
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post($this->apiUrl, [
                'target'  => $nomor,
                'message' => $pesan,
            ]);

            $body = $response->json();

            if (!$response->successful() || ($body['status'] ?? false) === false) {
                Log::warning('WhatsApp: gagal kirim.', [
                    'nomor'    => $nomor,
                    'response' => $body,
                ]);
                return false;
            }

            Log::info('WhatsApp: pesan terkirim.', ['nomor' => $nomor]);
            return true;

        } catch (\Throwable $e) {
            Log::error('WhatsApp: exception.', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function normalizeNomor(string $nomor): string
    {
        $nomor = preg_replace('/\D/', '', $nomor); // hapus non-digit

        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        } elseif (!str_starts_with($nomor, '62')) {
            $nomor = '62' . $nomor;
        }

        // Nomor valid: 10–15 digit
        return strlen($nomor) >= 10 && strlen($nomor) <= 15 ? $nomor : '';
    }
}