<?php

namespace App\Services;

use Illuminate\Support\Facades\Http; // <-- Import HTTP Client Laravel
use Illuminate\Support\Facades\Log;

class IplBillingService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        // Ambil data rahasia dari file config
        $this->apiUrl = config('services.ipl.api_url');
        $this->apiKey = config('services.ipl.api_key');
    }

    /**
     * Kirim data tagihan ke Sistem IPL Eksternal.
     *
     * @param int $userId (ID Member di sistem kita)
     * @param float $amount (Jumlah tagihan)
     * @param string $notes (Keterangan, misal: "Menginap 2 malam")
     * @return bool (True jika sukses, False jika gagal)
     */
    public function sendBilling(int $userId, float $amount, string $notes): bool
    {
        // Jika URL atau Key belum di-set di .env, jangan lakukan apa-apa
        if (!$this->apiUrl || !$this->apiKey) {
            Log::error('BILLING IPL GAGAL: API URL atau API Key belum di-set di .env');
            return false;
        }

        try {
            // 1. Siapkan data sesuai format yang diminta Tim IPL
            $payload = [
                'id_member_gudang' => $userId, // Kirim ID member kita
                'jumlah_tagihan' => $amount,
                'keterangan' => $notes,
                'sumber_tagihan' => 'GudangJababeka',
            ];
            
            // 2. Kirim ke API IPL menggunakan HTTP Client Laravel
            // (Contoh ini pakai Bearer Token, sesuaikan dengan autentikasi IPL-mu)
            $response = Http::withToken($this->apiKey)
                            ->timeout(10) // Timeout 10 detik
                            ->post($this->apiUrl, $payload);

            // 3. Cek respons
            if ($response->successful()) {
                // SUKSES
                Log::info('BILLING IPL SUKSES: Data terkirim.', $payload);
                return true;
            } else {
                // GAGAL
                Log::error('BILLING IPL GAGAL: Sistem IPL menolak.', [
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            // GAGAL (koneksi putus, timeout, dll)
            Log::error('BILLING IPL GAGAL: Koneksi error. ' . $e->getMessage());
            return false;
        }
    }
}