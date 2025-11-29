<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\IplBill;
use App\Models\Setting; // Asumsi nominal IPL disimpan di settings, atau hardcode
use Carbon\Carbon;

class GenerateMonthlyIpl extends Command
{
    protected $signature = 'ipl:generate';
    protected $description = 'Generate tagihan IPL bulanan untuk semua member';

    public function handle()
    {
        $this->info('Memulai generate tagihan IPL...');

        // 1. Tentukan Periode (Misal: "November 2025")
        $period = Carbon::now()->format('F Y');
        
        // 2. Tentukan Nominal (Bisa dari database settings atau hardcode)
        // Misal biaya IPL flat Rp 100.000
        $amount = 100000; 

        // 3. Ambil semua Member
        $members = User::where('role', 'member')->get();

        foreach ($members as $member) {
            // Cek apakah sudah ada tagihan bulan ini (biar gak dobel)
            $exists = IplBill::where('user_id', $member->id)
                             ->where('period', $period)
                             ->exists();

            if (!$exists) {
                // A. Buat Tagihan Baru
                IplBill::create([
                    'user_id' => $member->id,
                    'period' => $period,
                    'amount' => $amount,
                    'status' => 'unpaid'
                ]);

                // B. KUNCI AKSES MEMBER (Set jadi Unpaid)
                // Ini otomatis membuat QR Pribadi mereka DITOLAK di gerbang
                $member->ipl_status = 'unpaid';
                $member->save();
                
                $this->info("Tagihan dibuat untuk: {$member->name}");
            }
        }

        $this->info('Selesai generate tagihan.');
    }
}