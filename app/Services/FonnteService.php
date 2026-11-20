<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    /**
     * Normalisasi nomor HP dari format 08xxx menjadi 628xxx.
     * Menghapus semua karakter selain angka, lalu mengganti awalan 0 dengan 62.
     */
    private function normalizePhone(string $noHp): string
    {
        // Bersihkan selain angka
        $cleaned = preg_replace('/\D/', '', $noHp);

        if (empty($cleaned)) {
            return '';
        }

        // Jika diawali 08, ganti dengan 628
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        }

        return $cleaned;
    }

    /**
     * Kirim pesan WhatsApp melalui API Fonnte.
     * Jika token kosong, no_hp kosong, atau API gagal/timeout,
     * fungsi ini cukup return false tanpa melempar exception.
     */
    public function kirim(string $noHp, string $pesan): bool
    {
        $token = config('services.fonnte.token');

        // Guard: jangan kirim jika token atau nomor HP kosong
        if (empty($token) || empty(trim($noHp))) {
            return false;
        }

        $target = $this->normalizePhone($noHp);

        if (empty($target)) {
            return false;
        }

        try {
            $response = Http::timeout(8)
                ->connectTimeout(5)
                ->withHeaders([
                    'Authorization' => $token,
                ])
                ->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $pesan,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('[Fonnte] Kirim WA gagal: ' . $response->status() . ' - ' . $response->body());
            return false;

        } catch (\Throwable $e) {
            // API Fonnte timeout/gagal → tidak error 500, cukup log warning
            Log::warning('[Fonnte] Exception saat kirim WA: ' . $e->getMessage());
            return false;
        }
    }
}
