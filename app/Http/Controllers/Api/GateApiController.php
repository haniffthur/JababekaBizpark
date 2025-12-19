<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\PersonalQr;
use App\Models\Truck;
use App\Models\GateLog;
use App\Models\Setting; 
use App\Models\Billing;
// use App\Models\GateMachine; // Aktifkan jika sudah ada tabel gate_machines
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\IplBillingService;

class GateApiController extends Controller
{
    /**
     * =========================================================================
     * HANDLER UTAMA (Satu Pintu untuk Semua)
     * Rute: GET /api/gate
     * Parameter: qr_code, license_plate, termno, IO
     * =========================================================================
     */
    public function handleGateAccess(Request $request): JsonResponse
    {
        // 1. Validasi Input Dasar
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
            'license_plate' => 'required|string',
            'termno' => 'required|string', // Wajib ada ID Mesin
            'IO' => 'required|in:0,1',     // 0=Out, 1=In
        ]);

        if ($validator->fails()) {
            return $this->formatResponse(0, 'Input tidak lengkap: ' . $validator->errors()->first(), $request);
        }

        $io = (int) $request->input('IO');

        // 2. Arahkan ke Logika Masuk atau Keluar
        if ($io === 1) {
            return $this->processCheckIn($request);
        } else {
            return $this->processCheckOut($request);
        }
    }

    // =========================================================================
    // LOGIKA UTAMA (PRIVATE)
    // =========================================================================

    private function processCheckIn(Request $request): JsonResponse
    {
        $data = $request->all();
        
        // A. Cek QR Truk
        $qrTruk = QrCode::where('code', $data['qr_code'])->with('truck')->first();
        if ($qrTruk) return $this->handleQrTrukCheckIn($request, $qrTruk, $data['license_plate']);

        // B. Cek QR Pribadi
        $qrPribadi = PersonalQr::with('user')->where('code', $data['qr_code'])->first();
        if ($qrPribadi) return $this->handleQrPribadiCheckIn($request, $qrPribadi, $data['license_plate']);

        // Gagal
        $this->createGateLog(null, $request, 'Gagal Masuk', 'AksesDitolak');
        return $this->formatResponse(0, 'AksesDitolak', $request);
    }

    private function processCheckOut(Request $request): JsonResponse
    {
        $data = $request->all();

        // A. Cek QR Truk
        $qrTruk = QrCode::where('code', $data['qr_code'])->with('truck.user')->first();
        if ($qrTruk) return $this->handleQrTrukCheckOut($request, $qrTruk, $data['license_plate']);

        // B. Cek QR Pribadi
        $qrPribadi = PersonalQr::where('code', $data['qr_code'])->first();
        if ($qrPribadi) return $this->handleQrPribadiCheckOut($request, $qrPribadi, $data['license_plate']);

        // Gagal
        $this->createGateLog(null, $request, 'Gagal Keluar', 'QR Tidak Dikenal');
        return $this->formatResponse(0, 'QR Code tidak ditemukan.', $request);
    }

    // =========================================================================
    // HELPER RESPONSE JSON (FORMAT BARU)
    // =========================================================================
    private function formatResponse($status, $message, Request $request)
    {
        $direction = ($request->input('IO') == '1') ? 'In' : 'Out';
        
        return response()->json([
            "Status" => $status, // 1 = Sukses, 0 = Gagal
            "Date" => now()->format('d-m-Y H:i:s'),
            "Message" => $message,
            "QrCode" => $request->input('qr_code'),
            "Plat" => $request->input('license_plate'),
            "Direction" => $direction
        ]);
    }

    public function getMachineConfig(Request $request): JsonResponse
    {
        $termno = $request->query('termno');
        
        // Cari data mesin di database
        // (Pastikan Model GateMachine sudah di-import di atas: use App\Models\GateMachine;)
        $machine = \App\Models\GateMachine::where('termno', $termno)->first();

        if (!$machine) {
            return response()->json([
                'success' => false, 
                'message' => 'Mesin tidak terdaftar di Database'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'termno' => $machine->termno,
            'io_mode' => $machine->io_mode, // Mengambil settingan 1 (IN) atau 0 (OUT) dari DB
            'location' => $machine->location
        ]);
    }

    // =========================================================================
    // LOGIKA BISNIS SPESIFIK (TRUK VS PRIBADI)
    // =========================================================================

    private function handleQrTrukCheckIn(Request $request, QrCode $qrCode, string $licensePlate): JsonResponse
    {
        if ($qrCode->status !== 'baru') {
            $this->createGateLog($qrCode->truck_id, $request, 'Gagal Masuk', 'QR Truk sudah digunakan.');
            return $this->formatResponse(0, 'AKSESDITOLAK', $request);
        }
        if (!$qrCode->is_approved) {
            $this->createGateLog($qrCode->truck_id, $request, 'Gagal Masuk', 'QR Truk belum disetujui.');
            return $this->formatResponse(0, 'AKSESDITOLAK', $request);
        }
        if ($qrCode->truck->license_plate !== $licensePlate) {
            $this->createGateLog($qrCode->truck_id, $request, 'Gagal Masuk', 'Plat tidak cocok.');
            return $this->formatResponse(0, 'AKSESDITOLAK', $request);
        }
        if (!$qrCode->is_approved) {
            $this->createGateLog(null, $request, 'Gagal Masuk (Pribadi)', 'QR Pribadi belum disetujui Admin.', $qrCode->user_id);
            return $this->formatResponse(0, 'Akses Ditolak: QR Belum Disetujui Admin.', $request);
        }

        // Sukses
        $qrCode->status = 'aktif'; $qrCode->save();
        $qrCode->truck->is_inside = true; $qrCode->truck->save();
        GateLog::create(['truck_id' => $qrCode->truck_id, 'check_in_at' => now(), 'status' => 'Berhasil Masuk (Truk)']);
        
        return $this->formatResponse(1, 'Akses OK', $request);
    }

    private function handleQrPribadiCheckIn(Request $request, PersonalQr $qrCode, string $licensePlate): JsonResponse
    {
        if (!$qrCode->user) return $this->formatResponse(0, 'QR tidak valid (No User)', $request);
        
        if ($qrCode->user->ipl_status !== 'paid') {
            $this->createGateLog(null, $request, 'Gagal Masuk (Pribadi)', 'IPL Belum Lunas', $qrCode->user_id);
            return $this->formatResponse(0, 'BELUM LUNAS', $request);
        }
        if ($qrCode->status !== 'baru') {
            return $this->formatResponse(0, 'SDHDIDALAM', $request);
        }
        if ($qrCode->license_plate !== $licensePlate) {
            return $this->formatResponse(0, 'AksesDitolak', $request);
        }

        // Sukses
        $qrCode->status = 'aktif'; $qrCode->save();
        Truck::where('license_plate', $licensePlate)->update(['is_inside' => true]);
        
        GateLog::create([
            'user_id' => $qrCode->user_id,
            'license_plate' => $licensePlate,
            'check_in_at' => now(), 
            'status' => 'Berhasil Masuk (Pribadi)'
        ]);

        return $this->formatResponse(1, 'Akses OK', $request);
    }

   private function handleQrTrukCheckOut(Request $request, QrCode $qrCode, string $licensePlate): JsonResponse
    {
        if ($qrCode->status !== 'aktif' || $qrCode->truck->license_plate !== $licensePlate) {
            return $this->formatResponse(0, 'Plat tidak cocok atau QR tidak aktif.', $request);
        }

        $lastCheckInLog = GateLog::where('truck_id', $qrCode->truck_id)->where('status', 'Berhasil Masuk (Truk)')->latest('check_in_at')->first();
        if (!$lastCheckInLog) return $this->formatResponse(0, 'Log masuk tidak ditemukan.', $request);

        $checkInTime = Carbon::parse($lastCheckInLog->check_in_at);
        $checkOutTime = now();
        $billingAmount = 0;
        $billingNotes = 'Check-out normal.';
        
        // Cek Menginap
        if (!$checkInTime->isSameDay($checkOutTime)) {
            $nights = $checkInTime->diffInNights($checkOutTime);
            if ($nights == 0) $nights = 1;
            
            $overnightRate = (float) Setting::getValue('overnight_rate', 0);
            $billingAmount = $nights * $overnightRate;
            $billingNotes = "Menginap {$nights} malam @ Rp " . number_format($overnightRate, 0, ',', '.');
            
            // --- KEMBALI KE LOGIKA BILLING LANGSUNG ---
            $billingMode = Setting::getValue('billing_integration_mode', 'local');
            
            if ($billingMode == 'ipl') {
                 try {
                    (new IplBillingService())->sendBilling($qrCode->truck->user->id, $billingAmount, $billingNotes);
                 } catch (\Exception $e) { Log::error($e->getMessage()); }
            } else {
                // DIRECT BILLING (Langsung buat tagihan)
                Billing::create([
                    'user_id' => $qrCode->truck->user_id,
                    'total_amount' => $billingAmount,
                    'status' => 'pending',
                    'due_date' => now()->addDays(14),
                    // 'description' => $billingNotes // Jika ada kolom description
                ]);
            }
        }

        $qrCode->status = 'selesai'; $qrCode->save();
        $qrCode->truck->is_inside = false; $qrCode->truck->save();
        
        GateLog::create([
            'truck_id' => $qrCode->truck_id, 
            'check_in_at' => $checkInTime,
            'check_out_at' => now(), 
            'status' => 'Berhasil Keluar (Truk)', 
            'notes' => $billingNotes, 
            'billing_amount' => $billingAmount
        ]);

        return $this->formatResponse(1, 'SampaiJumpa', $request);
    }

    private function handleQrPribadiCheckOut(Request $request, PersonalQr $qrCode, string $licensePlate): JsonResponse
    {
        if ($qrCode->status !== 'aktif' || $qrCode->license_plate !== $licensePlate) {
            return $this->formatResponse(0, 'AKSESDITOLAK', $request);
        }

         if ($qrCode->user->ipl_status !== 'paid') {
            $this->createGateLog(null, $request, 'Gagal Masuk (Pribadi)', 'IPL Belum Lunas', $qrCode->user_id);
            return $this->formatResponse(0, 'BELUM LUNAS', $request);
        }

        $qrCode->status = 'baru'; // Reset
        $qrCode->save();
        Truck::where('license_plate', $licensePlate)->update(['is_inside' => false]);

        GateLog::create([
            'user_id' => $qrCode->user_id,
            'license_plate' => $licensePlate,
            'check_out_at' => now(),
            'status' => 'Berhasil Keluar (Pribadi)'
        ]);

        return $this->formatResponse(1, 'SampaiJumpa', $request);
    }

    // Helper untuk log gagal (Jika perlu)
    private function createGateLog($truckId, Request $request, string $status, string $notes, $userId = null): void
    {
        GateLog::create([
            'truck_id' => $truckId,
            'user_id' => $userId,
            'license_plate' => $request->input('license_plate'),
            'status' => $status,
            'notes' => $notes . " [Term: " . $request->input('termno') . "]",
        ]);
    }
}       