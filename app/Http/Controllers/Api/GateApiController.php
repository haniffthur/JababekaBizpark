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

class GateApiController extends Controller
{
    public function handleGateAccess(Request $request): JsonResponse
    {
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
        return $this->formatResponse(0, 'Akses Ditolak', $request);
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
            $this->createGateLog($qrCode->truck_id, $request, 'Gagal Masuk', 'Validasi Gagal');
            return $this->formatResponse(0, 'AKSES DITOLAK', $request);
        }

        $qrCode->update(['status' => 'aktif']);
        $qrCode->truck->update(['is_inside' => true]);
        
        GateLog::create(['truck_id' => $qrCode->truck_id, 'check_in_at' => now(), 'status' => 'Berhasil Masuk (Truk)']);
        return $this->formatResponse(1, 'Silakan Masuk', $request);
    }

    private function handleQrTrukCheckOut(Request $request, QrCode $qrCode, string $plat): JsonResponse
    {
        if ($qrCode->status !== 'aktif' || $qrCode->truck->license_plate !== $plat) {
            return $this->formatResponse(0, 'Validasi Gagal', $request);
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

        return $this->formatResponse(1, 'Sampai Jumpa', $request);
    }

    // --- LOGIKA PRIBADI ---
    private function handleQrPribadiCheckIn(Request $request, PersonalQr $qr, string $plat): JsonResponse
    {
        if ($qr->user->ipl_status !== 'paid') return $this->formatResponse(0, 'BELUM LUNAS', $request);
        if ($qr->status !== 'baru' || $qr->license_plate !== $plat) return $this->formatResponse(0, 'Ditolak', $request);

        $qr->update(['status' => 'aktif']);
        Truck::where('license_plate', $plat)->update(['is_inside' => true]); // Update flag global
        GateLog::create(['user_id' => $qr->user_id, 'license_plate' => $plat, 'check_in_at' => now(), 'status' => 'Berhasil Masuk (Pribadi)']);
        
        return $this->formatResponse(1, 'Silakan Masuk', $request);
    }

    private function handleQrPribadiCheckOut(Request $request, PersonalQr $qr, string $plat): JsonResponse
    {
        if ($qr->status !== 'aktif' || $qr->license_plate !== $plat) return $this->formatResponse(0, 'Ditolak', $request);
        if ($qr->user->ipl_status !== 'paid') return $this->formatResponse(0, 'BELUM LUNAS', $request);

        $qr->update(['status' => 'baru']);
        Truck::where('license_plate', $plat)->update(['is_inside' => false]);
        GateLog::create(['user_id' => $qr->user_id, 'license_plate' => $plat, 'check_out_at' => now(), 'status' => 'Berhasil Keluar (Pribadi)']);
        
        return $this->formatResponse(1, 'Sampai Jumpa', $request);
    }

    private function formatResponse($status, $msg, Request $request) {
        return response()->json([
            "Status" => $status, "Date" => now()->format('d-m-Y H:i:s'), "Message" => $msg,
            "QrCode" => $request->input('qr_code'), "Direction" => ($request->input('IO')==1)?'In':'Out'
        ]);
    }

    private function createGateLog($tId, $req, $status, $notes, $uId=null) {
        GateLog::create(['truck_id'=>$tId, 'user_id'=>$uId, 'license_plate'=>$req->input('license_plate'), 'status'=>$status, 'notes'=>$notes]);
    }
}