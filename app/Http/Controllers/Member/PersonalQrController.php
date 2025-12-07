<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\PersonalQr;
use Illuminate\Http\Request; // <-- Import Request
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse; // <-- Import JsonResponse
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode as SimpleQrCode;
use Illuminate\Support\Str; // Jangan lupa import
use Illuminate\Support\Facades\Validator;

class PersonalQrController extends Controller
{
    /**
     * Menampilkan daftar QR Code Pribadi.
     * Merespons HTML (Load Awal) atau HTML Partial (AJAX Polling).
     */
    public function index(Request $request): View|string
    {
      $personalQrs = Auth::user()->personalQrs()
                                   ->where('is_approved', true) // <-- TAMBAHKAN INI
                                   ->get();

        // Cek jika ini request AJAX (polling)
        if ($request->ajax()) {
            // Jika AJAX, kita render HANYA bagian partial-nya
            return view('member.personal_qrs.partials.qr_list', compact('personalQrs'))->render();
        }   

        // Jika request normal (load halaman), kirim view penuh
        return view('member.personal_qrs.index', compact('personalQrs'));
    }
    public function printQr(PersonalQr $qrcode): View
    {
        // Otorisasi sederhana (pastikan QR ini milik user)
        if ($qrcode->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Kembalikan view cetak yang baru
        return view('member.personal_qrs.print', compact('qrcode'));
    }
    public function downloadPDF(PersonalQr $qrcode)
    {
        // Otorisasi
        if ($qrcode->user_id !== Auth::id()) {
            abort(403);
        }

        // 1. Generate QR Code sebagai PNG Base64 (Anti-gagal)
        $qrCodeImage = 'data:image/png;base64,' . base64_encode(
            SimpleQrCode::format('png')->size(300)->errorCorrection('H')->generate($qrcode->code)
        );

        // 2. Data yang akan dikirim ke view PDF
        $data = [
            'qrcode' => $qrcode,
            'qrCodeImage' => $qrCodeImage 
        ];

        // 3. Render view 'pdf.blade.php' yang baru
        $pdf = Pdf::loadView('member.personal_qrs.pdf', $data);
        
        // Atur nama file
        $fileName = 'qr-pribadi-' . $qrcode->license_plate . '-' . $qrcode->id . '.pdf';

        // Download PDF-nya
        return $pdf->download($fileName);
    }
    public function store(Request $request)
    {
        // Validasi Status IPL (Member nunggak gabisa request)
        if (Auth::user()->ipl_status !== 'paid') {
            return back()->with('error', 'Lunasi tagihan IPL Anda sebelum request QR baru.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50', // Misal: "Mobil Istri"
            'license_plate' => 'required|string|max:20|unique:personal_qrs,license_plate',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Format Plat (Hapus spasi)
        $cleanPlate = strtoupper(str_replace(' ', '', $request->license_plate));

        // Buat QR (Status Approved = False)
        PersonalQr::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'license_plate' => $cleanPlate,
            'code' => now()->format('dmY') . '' . strtoupper(Str::random(2)),
            'status' => 'baru',
            'is_approved' => false, // <--- PENTING: Menunggu Admin
        ]);

        return back()->with('success', 'Permintaan QR Pribadi berhasil dikirim. Menunggu persetujuan Admin.');
    }
    public function checkMyRequests(): JsonResponse
    {
        $requests = PersonalQr::where('user_id', auth()->id())->get();
        
        // Kita kirim data statusnya saja untuk dibandingkan di JS
        $data = $requests->map(function($q) {
            return [
                'id' => $q->id,
                'status' => $q->status,
                'is_approved' => $q->is_approved,
                'plat' => $q->license_plate
            ];
        });

        return response()->json(['requests' => $data]);
    }
}