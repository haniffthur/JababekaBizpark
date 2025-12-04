<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\Truck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse; // <-- Tambahkan JsonResponse


// Import SimpleSoftwareIO/Simple-QRcode
use SimpleSoftwareIO\QrCode\Facades\QrCode as SimpleQrCode; 

class QrCodeController extends Controller
{
    /**
     * Menampilkan daftar QR Code milik member yang sedang login.
     * Rute: GET member/qrcodes
     */
 // app/Http/Controllers/Member/QrCodeController.php
public function index(Request $request): View|JsonResponse
    {
        $truckIds = Auth::user()->trucks()->pluck('id');

        // Cek jika ini request AJAX (polling)
        if ($request->ajax()) {
            // 1. Ambil data Approved (tidak perlu paginasi untuk polling real-time)
            $approvedQrs = QrCode::whereIn('truck_id', $truckIds)
                                 ->where('is_approved', true)
                                 ->with('truck') 
                                 ->latest()
                                 ->get(); // Ambil semua/terbaru

            // 2. Ambil data Pending
            $pendingQrs = QrCode::whereIn('truck_id', $truckIds)
                                ->where('is_approved', false)
                                ->with('truck')
                                ->latest()
                                ->get();
            
            return response()->json([
                'approvedQrs' => $approvedQrs,
                'pendingQrs' => $pendingQrs
            ]);
        }

        // --- Jika ini request Normal (Load Halaman) ---

        // 1. QR Codes Siap Digunakan (Approved) - Dengan Paginasi
        $qrCodes = QrCode::whereIn('truck_id', $truckIds)
                         ->where('is_approved', true)
                         ->with('truck') 
                         ->latest()
                         ->paginate(15);

        // 2. Permintaan yang Masih Diproses (Pending)
        $pendingQrs = QrCode::whereIn('truck_id', $truckIds)
                            ->where('is_approved', false)
                            ->with('truck')
                            ->latest()
                            ->get();
        
        return view('member.qrcodes.index', compact('qrCodes', 'pendingQrs'));
    }

    /**
     * Menampilkan form untuk membuat QR Code baru.
     * Rute: GET member/qrcodes/create
     */
    public function create(): View
    {
        $trucks = Auth::user()->trucks()->orderBy('license_plate')->get();
        return view('member.qrcodes.create', compact('trucks'));
    }

    /**
     * Menyimpan QR Code baru ke database.
     * Rute: POST member/qrcodes
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'truck_id' => 'required|exists:trucks,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $truck = Truck::find($request->truck_id);
        if ($truck->user_id !== Auth::id()) {
            abort(403, 'Anda tidak diizinkan membuat QR untuk truk ini.');
        }
$uniqueCode = now()->format('dmY') . strtoupper(Str::random(2));

        QrCode::create([
        'truck_id' => $request->truck_id,
        'code' => $uniqueCode,
        'status' => 'baru', 
        'is_approved' => false, // <-- KUNCI: Diset false secara default
    ]);

    return redirect()->route('member.qrcodes.index')
        ->with('success', 'Permintaan QR Code berhasil dibuat. Menunggu konfirmasi Admin.');
    }

    /**
     * Menampilkan halaman detail QR Code (Web View).
     * Rute: GET member/qrcodes/{qrcode}
     */
    public function show(QrCode $qrcode): View
    {
        Gate::authorize('manage-qrcode', $qrcode);
        $qrcode->load('truck');
        
        // Web View akan menggunakan SimpleQrCode langsung di Blade
        return view('member.qrcodes.show', compact('qrcode'));
    }

    /**
     * Men-download halaman QR Code sebagai PDF. (FIX IMAGICK)
     * Rute: GET member/qrcodes/{qrcode}/download
     */
    public function downloadPDF(QrCode $qrcode)
    {
        Gate::authorize('manage-qrcode', $qrcode);
        $qrcode->load('truck');

        // 1. GENERATE QR CODE MENGGUNAKAN SIMPLE QR CODE
        // Kita paksa output PNG Base64 di Controller untuk menghindari konflik Dompdf/Imagick.
        
        $qrCodeImage = 'data:image/png;base64,' . base64_encode(
            SimpleQrCode::format('png')->size(300)->errorCorrection('H')->generate($qrcode->code)
        );

        // 2. Data yang akan dikirim ke view PDF
        $data = [
            'qrcode' => $qrcode,
            'qrCodeImage' => $qrCodeImage 
        ];

        // 3. Render view 'pdf.blade.php'
        $pdf = Pdf::loadView('member.qrcodes.pdf', $data);
        $fileName = 'qr-' . $qrcode->truck->license_plate . '-' . $qrcode->id . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Menghapus QR Code.
     * Rute: DELETE member/qrcodes/{qrcode}
     */
    public function destroy(QrCode $qrcode): RedirectResponse
    {
        Gate::authorize('manage-qrcode', $qrcode);
        
        if ($qrcode->status === 'aktif') {
            return back()->with('error', 'Tidak bisa menghapus QR Code yang sedang aktif (di dalam gudang).');
        }
        
        try {
            $qrcode->delete();
            return redirect()->route('member.qrcodes.index')->with('success', 'QR Code berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus QR Code.');
        }
    }
    
}