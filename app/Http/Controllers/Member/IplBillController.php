<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\IplBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IplBillController extends Controller
{
    // Tampilkan Daftar Tagihan IPL
    public function index()
    {
        $bills = IplBill::where('user_id', Auth::id())->latest()->paginate(10);
        return view('member.ipl_bills.index', compact('bills'));
    }

    // Proses Bayar (Simulasi)
    public function pay(Request $request, IplBill $iplBill)
    {
        // 1. Validasi Kepemilikan & Input
        if ($iplBill->user_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
        ]);

        // 2. Upload Gambar
        if ($request->hasFile('proof_image')) {
            // Simpan ke folder 'public/proofs'
            $path = $request->file('proof_image')->store('proofs', 'public');
            
            $iplBill->proof_image = $path;
        }

        // 3. Update Status
        // (Dalam sistem nyata, mungkin statusnya 'pending_verification' dulu)
        // Tapi untuk otomatisasi yang kamu minta, kita set langsung 'paid'
        $iplBill->status = 'paid';
        $iplBill->paid_at = now();
        $iplBill->save();

        // 4. Buka Akses Gerbang (Jika semua lunas)
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