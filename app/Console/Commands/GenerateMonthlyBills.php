<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Billing;
use App\Models\DailyCharge; // Model baru
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyBills extends Command
{
    protected $signature = 'billing:monthly';
    protected $description = 'Rekap tagihan bulanan (IPL + Inap) dan blokir member';

    public function handle()
    {
        $this->info('Mulai Generate Tagihan Bulanan...');

        // Ambil nominal IPL dari Setting (Default 100rb)
        $nominalIPL = (float) Setting::where('key', 'ipl_fee')->value('value') ?? 100000;
        
        // Ambil member aktif
        $members = User::where('role', 'member')->get(); // Sesuaikan query member Anda

        foreach ($members as $member) {
            // Gunakan Transaction agar Data Konsisten
            DB::transaction(function () use ($member, $nominalIPL) {
                
                // A. Ambil Biaya Inap yang Belum Ditagih (Status: is_billed = false)
                // Kita ambil semua yang menumpuk dari bulan lalu sampai detik ini
                $pendingCharges = DailyCharge::where('user_id', $member->id)
                                    ->where('is_billed', false)
                                    ->get();

                // B. Hitung Total
                $totalInap = $pendingCharges->sum('amount');
                $grandTotal = $nominalIPL + $totalInap;

                // C. Buat Tagihan (Billing)
                $description = "Iuran Pengelolaan Lingkungan (IPL): Rp " . number_format($nominalIPL);
                if ($totalInap > 0) {
                    $description .= " + Biaya Inap Truk (x" . $pendingCharges->count() . " kejadian): Rp " . number_format($totalInap);
                }

                $newBill = Billing::create([
                    'user_id'      => $member->id,
                    'total_amount' => $grandTotal,
                    'status'       => 'unpaid',
                    'due_date'     => Carbon::now()->addDays(10), // Jatuh tempo tgl 10
                    'description'  => $description
                ]);

                // D. Update DailyCharges (Tandai sudah ditagih agar tidak dobel bulan depan)
                if ($pendingCharges->count() > 0) {
                    DailyCharge::whereIn('id', $pendingCharges->pluck('id'))
                        ->update([
                            'is_billed' => true,
                            'ipl_bill_id' => $newBill->id
                        ]);
                }

                // E. Blokir Akses Member (Sampai bayar)
                $member->ipl_status = 'unpaid';
                $member->save();

            }); // End Transaction

            $this->info("Tagihan User {$member->name} Selesai.");
        }

        $this->info('Semua proses selesai.');
    }
}