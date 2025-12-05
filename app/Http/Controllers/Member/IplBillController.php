<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\IplBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Member\BillingController;
use App\Models\User;

class IplBillController extends Controller
{
    // Tampilkan Daftar Tagihan IPL
    public function index()
{
    // Ambil billing dimana user_id adalah user yang sedang login
 $billings = $billings = IplBill::where('user_id', Auth::id())
                   ->latest()
                   ->paginate(10);


    return view('member.billings.index', compact('billings'));
}

    // Proses Bayar (Simulasi)
    public function pay(Request $request, IplBill $iplBill)
    {
        // 1. Validasi Kepemilikan
        if ($iplBill->user_id !== Auth::id()) {
            abort(403);
        }
        
        // 2. Validasi Input (File Gambar)
        $request->validate([
            'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
        ]);

        // 3. Handle File Upload
        if ($request->hasFile('proof_image')) {
            // Simpan ke folder 'public/proofs'
            // Pastikan sudah run: php artisan storage:link
            $path = $request->file('proof_image')->store('proofs', 'public');
            
            $iplBill->proof_image = $path;
        }

        // 4. Update Status
        $iplBill->status = 'paid';
        $iplBill->paid_at = now();
        $iplBill->save();

        // 5. Buka Akses Gerbang (Jika semua lunas)
        $unpaidCount = IplBill::where('user_id', Auth::id())
                              ->where('status', 'unpaid')
                              ->count();

        if ($unpaidCount == 0) {
            $user = Auth::user();
            $user->ipl_status = 'paid';
            $user->save();
        }

        return back()->with('success', 'Bukti pembayaran diterima! Akses gerbang Anda aktif kembali.');
    }
}