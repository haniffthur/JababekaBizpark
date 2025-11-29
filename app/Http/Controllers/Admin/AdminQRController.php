<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use Illuminate\Http\Request; // Import Request
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse; // Import JsonResponse

class AdminQrController extends Controller
{
    /**
     * Menampilkan daftar QR Code yang menunggu persetujuan.
     * Rute: GET /admin/qr-approvals
     * Merespons HTML atau JSON (untuk AJAX)
     */
    public function index(Request $request): View|JsonResponse
    {
        // Query dasar untuk mengambil QR yang menunggu persetujuan
        $query = QrCode::where('is_approved', false)
                       ->where('status', 'baru')
                       ->with('truck.user') // Eager load relasi
                       ->latest();

        // Cek jika ini request AJAX (polling)
        if ($request->ajax()) {
            $qrCodes = $query->get(); // Ambil semua (atau take(20) jika terlalu banyak)
            
            return response()->json([
                'data' => $qrCodes
            ]);
        }

        // Jika request normal (load halaman), gunakan paginasi
        $qrCodes = $query->paginate(15);
        
        return view('admin.qr_approvals.index', compact('qrCodes'));
    }

    /**
     * Menyetujui QR Code.
     * Rute: POST /admin/qr-approvals/{qrcode}/approve
     */
    public function approve(QrCode $qrcode): RedirectResponse
    {
        if ($qrcode->is_approved || $qrcode->status !== 'baru') {
            return back()->with('error', 'QR Code ini tidak memenuhi syarat persetujuan.');
        }

        $qrcode->is_approved = true;
        $qrcode->save();

        return redirect()->route('admin.qr.approvals.index')
                         ->with('success', 'QR Code berhasil disetujui dan siap digunakan.');
    }
}