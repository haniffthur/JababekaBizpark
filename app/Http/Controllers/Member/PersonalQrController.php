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

class PersonalQrController extends Controller
{
    /**
     * Menampilkan daftar QR Code Pribadi.
     * Merespons HTML (Load Awal) atau HTML Partial (AJAX Polling).
     */
    public function index(Request $request): View|string
    {
        $personalQrs = Auth::user()->personalQrs()->get();

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
}