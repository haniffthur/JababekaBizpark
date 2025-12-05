<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Billing;
use Carbon\Carbon;

class GenerateMonthlyBills extends Command
{
    protected $signature = 'billing:monthly';
    protected $description = 'Generate tagihan bulanan dan blokir akses member yang belum bayar';

    public function handle()
    {
        $this->info('Memulai proses tagihan bulanan...');

        $members = User::where('role', 'member')->get();
        $bulanIni = Carbon::now()->startOfMonth();
        
        // Nominal IPL (Bisa diambil dari SettingController nanti)
        $nominalIPL = 100000; 

        foreach ($members as $member) {
            // 1. Cek apakah sudah ada tagihan bulan ini?
            // Kita cek range tanggal pembuatan tagihan
            $cekTagihan = Billing::where('user_id', $member->id)
                ->whereBetween('created_at', [$bulanIni, Carbon::now()->endOfMonth()])
                ->exists();

            if (!$cekTagihan) {
                // 2. Buat Tagihan Baru
                Billing::create([
                    'user_id' => $member->id,
                    'total_amount' => $nominalIPL,
                    'status' => 'unpaid',
                    'due_date' => Carbon::now()->addDays(10), // Jatuh tempo tgl 10
                ]);
                
                // 3. BLOKIR AKSES (Sesuai Request Poin 4)
                // Member langsung jadi 'unpaid' begitu tanggal 1 muncul
                $member->ipl_status = 'unpaid';
                $member->save();

                $this->info("Tagihan dibuat & Akses diblokir untuk: " . $member->name);
            }
        }

        $this->info('Selesai.');
    }
}