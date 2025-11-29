<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Billing; // Import model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Auth
use Illuminate\Support\Facades\Gate; // Import Gate
use Illuminate\View\View;

class BillingController extends Controller
{
    /**
     * Menampilkan daftar tagihan MILIK user yang sedang login.
     * Rute: GET member/billings
     */
    public function index(): View
    {
        // Ambil data tagihan HANYA milik user yang login
        $billings = Auth::user()->billings()->latest()->paginate(15);
        
        return view('member.billings.index', compact('billings'));
    }

    /**
     * Menampilkan detail satu tagihan.
     * Rute: GET member/billings/{billing}
     */
    public function show(Billing $billing): View
    {
        // Otorisasi pakai Gate 'view-billing' yang sudah kita buat
        // Ini akan cek: (Apakah user admin ATAU user_id di tagihan == auth->id)
        Gate::authorize('view-billing', $billing);

        // Load relasi user (walaupun sudah pasti miliknya, untuk konsistensi view)
        $billing->load('user');

        return view('member.billings.show', compact('billing'));
    }
}