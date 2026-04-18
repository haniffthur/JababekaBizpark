<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\PersonalQr;
use App\Models\Truck;
use App\Models\GateLog;
use App\Models\Setting;
use App\Models\DailyCharge; // Wajib import ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Models\MasterQr;

class GateApiController extends Controller
{
    public function handleGateAccess(Request $request): JsonResponse
{
    // --- 1. JALUR PINTAS (BYPASS) MASTER QR ---
    if ($request->license_plate === 'PLAT MASTER') {
        $masterQr = MasterQr::where('code', $request->qr_code)->where('is_active', true)->first();
        
        if ($masterQr) {
            $status = ($request->IO == 1) ? 'Berhasil Masuk (Master)' : 'Berhasil Keluar (Master)';
            // Simpan log, plat dikosongkan atau diisi MASTER
            $this->createGateLog(null, $request, $status, "Digunakan oleh: {$masterQr->name}");
            
            // Berikan status = 1 (Buka Gerbang)
            return $this->formatResponse(1, "Akses VIP:   {$masterQr->name}", $request);
        }
        
        // Kalau kodenya gak ada di tabel MasterQr, tolak (kasih status = 0).
        // Nanti Python bakal tau dan lanjut nunggu kamera LPR.
        return $this->formatResponse(0, 'Bukan QR Master', $request);
    }

    // --- 2. VALIDASI NORMAL (Truk / Pribadi Biasa) ---
   $originalPlate = $request->license_plate;
    
    // 1. Coba cari plat yang mirip di tabel Truck
    $matchedPlate = $this->findSimilarPlate($originalPlate, 'truck');
    
    // 2. Jika tidak ketemu di Truck, coba cari di PersonalQr
    if (!$matchedPlate) {
        $matchedPlate = $this->findSimilarPlate($originalPlate, 'personal');
    }

    // 3. Jika ditemukan yang mirip (jarak <= 1), timpa nilai plat di request
    if ($matchedPlate) {
        $request->merge(['license_plate' => $matchedPlate]);
        // Opsional: Log bahwa terjadi koreksi plat
        // Log::info("Plate corrected from $originalPlate to $matchedPlate");
    }

    // --- SEKARANG LANJUT KE VALIDASI NORMAL DENGAN PLAT YANG SUDAH DIKOREKSI ---
    $validator = Validator::make($request->all(), [
        'qr_code' => 'required|string',
        'license_plate' => 'required|string', 
        'termno' => 'required|string',
        'IO' => 'required|in:0,1',
    ]);

    if ($validator->fails()) {
        return $this->formatResponse(0, 'Input tidak lengkap', $request);
    }

    return ($request->input('IO') == 1) 
        ? $this->processCheckIn($request) 
        : $this->processCheckOut($request);
}

    private function processCheckIn(Request $request): JsonResponse
    {
        $data = $request->all();
        
        // Cek Truk
        $qrTruk = QrCode::where('code', $data['qr_code'])->with('truck')->first();
        if ($qrTruk) return $this->handleQrTrukCheckIn($request, $qrTruk, $data['license_plate']);

        // Cek Pribadi
        $qrPribadi = PersonalQr::with('user')->where('code', $data['qr_code'])->first();
        if ($qrPribadi) return $this->handleQrPribadiCheckIn($request, $qrPribadi, $data['license_plate']);

        $this->createGateLog(null, $request, 'Gagal Masuk', 'QR Tidak Dikenal');
        return $this->formatResponse(0, 'maafditolak', $request);
    }

    private function processCheckOut(Request $request): JsonResponse
    {
        $data = $request->all();

        // Cek Truk
        $qrTruk = QrCode::where('code', $data['qr_code'])->with('truck.user')->first();
        if ($qrTruk) return $this->handleQrTrukCheckOut($request, $qrTruk, $data['license_plate']);

        // Cek Pribadi
        $qrPribadi = PersonalQr::where('code', $data['qr_code'])->first();
        if ($qrPribadi) return $this->handleQrPribadiCheckOut($request, $qrPribadi, $data['license_plate']);

        $this->createGateLog(null, $request, 'Gagal Keluar', 'QR Tidak Dikenal');
        return $this->formatResponse(0, 'QR Tidak Dikenal', $request);
    }

    // --- LOGIKA TRUK ---
    private function handleQrTrukCheckIn(Request $request, QrCode $qrCode, string $plat): JsonResponse
    {
        if ($qrCode->status !== 'baru' || !$qrCode->is_approved || $qrCode->truck->license_plate !== $plat) {
            $this->createGateLog($qrCode->truck_id, $request, 'Gagal Masuk', 'maafDITOLAK');
            return $this->formatResponse(0, 'maafditolak', $request);
        }

        $qrCode->update(['status' => 'aktif']);
        $qrCode->truck->update(['is_inside' => true]);
        
        GateLog::create(['truck_id' => $qrCode->truck_id, 'check_in_at' => now(), 'status' => 'Berhasil Masuk (Truk)']);
        return $this->formatResponse(1, 'SilaknMasuk', $request);
    }

    private function handleQrTrukCheckOut(Request $request, QrCode $qrCode, string $plat): JsonResponse
    {
        if ($qrCode->status !== 'aktif' || $qrCode->truck->license_plate !== $plat) {
            return $this->formatResponse(0, 'maafditolak', $request);
        }

        $lastLog = GateLog::where('truck_id', $qrCode->truck_id)
            ->where('status', 'Berhasil Masuk (Truk)')->latest('check_in_at')->first();

        if (!$lastLog) return $this->formatResponse(0, 'Log Masuk Hilang', $request);

        $in = Carbon::parse($lastLog->check_in_at);
        $out = now();
        $notes = 'Check-out normal.';

        // LOGIKA MENGINAP (SIMPAN KE DAILY CHARGES)
        if (!$in->isSameDay($out)) {
            $nights = $in->diffInNights($out) ?: 1;
            $rate = (float) Setting::where('key', 'overnight_rate')->value('value') ?? 50000;
            $cost = $nights * $rate;

            DailyCharge::create([
                'user_id' => $qrCode->truck->user_id,
                'truck_id' => $qrCode->truck_id,
                'amount' => $cost,
                'charge_date' => now(),
                'is_billed' => false
            ]);

            $notes = "Menginap $nights malam. Biaya Rp " . number_format($cost);
        }

        $qrCode->update(['status' => 'selesai']);
        $qrCode->truck->update(['is_inside' => false]);

        GateLog::create([
            'truck_id' => $qrCode->truck_id, 'check_in_at' => $in, 'check_out_at' => $out,
            'status' => 'Berhasil Keluar (Truk)', 'notes' => $notes, 'billing_amount' => 0
        ]);

        return $this->formatResponse(1, 'SampaiJumpa', $request);
    }

    // --- LOGIKA PRIBADI ---
    private function handleQrPribadiCheckIn(Request $request, PersonalQr $qr, string $plat): JsonResponse
    {
        if ($qr->user->ipl_status !== 'paid') return $this->formatResponse(0, 'BELUM LUNAS', $request);
        if ($qr->status !== 'baru' || $qr->license_plate !== $plat) return $this->formatResponse(0, 'PASSBACK', $request);

        $qr->update(['status' => 'aktif']);
        Truck::where('license_plate', $plat)->update(['is_inside' => true]); // Update flag global
        GateLog::create(['user_id' => $qr->user_id, 'license_plate' => $plat  , 'check_in_at' => now(), 'status' => 'Berhasil Masuk (Pribadi)']);
        
        return $this->formatResponse(1, 'Silaknmasuk', $request);
    }

    private function handleQrPribadiCheckOut(Request $request, PersonalQr $qr, string $plat): JsonResponse
    {
        if ($qr->status !== 'aktif' || $qr->license_plate !== $plat) return $this->formatResponse(0, 'PASSBACK', $request);
        if ($qr->user->ipl_status !== 'paid') return $this->formatResponse(0, 'BELUM LUNAS', $request);

        $qr->update(['status' => 'baru']);
        Truck::where('license_plate', $plat)->update(['is_inside' => false]);
        GateLog::create(['user_id' => $qr->user_id, 'license_plate' => $plat, 'check_out_at' => now(), 'status' => 'Berhasil Keluar (Pribadi)']);
        
        return $this->formatResponse(1, 'SAMPAIJUMPA', $request);
    }

    private function formatResponse($status, $msg, Request $request) {
    return response()->json([
        "Status"    => $status,
        "Date"      => now()->format('d-m-Y H:i:s'),
        "Message"   => $msg,
         "Plat"      => $request->input('license_plate')  ,
        "QrCode"    => $request->input('qr_code'),
        // Tambahkan baris ini
        "Direction" => ($request->input('IO') == 1) ? 'In' : 'Out'
    ]);
}

    private function createGateLog($tId, $req, $status, $notes, $uId=null) {
        GateLog::create(['truck_id'=>$tId, 'user_id'=>$uId, 'license_plate'=>$req->input('license_plate'), 'status'=>$status, 'notes'=>$notes]);
    }

    // Tambahkan di dalam class GateApiController

private function findSimilarPlate($inputPlate, $type = 'truck')
{
    $inputPlate = strtoupper(str_replace(' ', '', $inputPlate));
    
    // Ambil semua kandidat plat nomor yang aktif dari database
    if ($type === 'truck') {
        $candidates = Truck::pluck('license_plate')->toArray();
    } else {
        $candidates = PersonalQr::pluck('license_plate')->toArray();
    }

    foreach ($candidates as $candidate) {
        $cleanCandidate = strtoupper(str_replace(' ', '', $candidate));
        
        // Hitung jarak perbedaan karakter
        $distance = levenshtein($inputPlate, $cleanCandidate);
        
        // Jika perbedaan maksimal 1 digit/karakter
        if ($distance <= 1) {
            return $candidate; // Kembalikan plat asli dari database
        }
    }

    return null;
}
}