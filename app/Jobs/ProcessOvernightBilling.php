<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\GateLog;
use App\Models\Setting;
use App\Models\DailyCharge;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessOvernightBilling implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $gateLogId;
    protected $checkInTime;
    protected $checkOutTime;
    protected $userId;
    protected $truckId;

    public function __construct($gateLogId, $checkInTime, $checkOutTime, $userId, $truckId)
    {
        $this->gateLogId = $gateLogId;
        $this->checkInTime = $checkInTime;
        $this->checkOutTime = $checkOutTime;
        $this->userId = $userId;
        $this->truckId = $truckId;
    }

    public function handle()
    {
        $in = Carbon::parse($this->checkInTime);
        $out = Carbon::parse($this->checkOutTime);

        // LOGIKA: Jika tanggal masuk beda dengan tanggal keluar = MENGINAP
        if (!$in->isSameDay($out)) {
            
            // 1. Hitung Malam
            $nights = $in->diffInNights($out);
            if ($nights == 0) $nights = 1; // Jaga-jaga lintas hari durasi pendek

            // 2. Ambil Tarif dari Setting (Default 50rb jika setting kosong)
            $overnightRate = (float) Setting::where('key', 'overnight_rate')->value('value') ?? 50000;
            
            // 3. Hitung Total
            $totalCost = $nights * $overnightRate;

            // 4. SIMPAN KE TABEL SEMENTARA (DailyCharge)
            DailyCharge::create([
                'user_id'     => $this->userId,
                'truck_id'    => $this->truckId,
                'amount'      => $totalCost,
                'charge_date' => now(),
                'is_billed'   => false, // Penting: Belum ditagih
            ]);

            // 5. Update Log Gate (Hanya untuk info history)
            $log = GateLog::find($this->gateLogId);
            if ($log) {
                $log->notes = "Menginap {$nights} malam. Tercatat di DailyCharge: Rp " . number_format($totalCost);
                $log->billing_amount = $totalCost; // Sekedar info
                $log->save();
            }
        }
    }
}