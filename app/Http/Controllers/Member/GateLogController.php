<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\GateLog;
use Illuminate\Http\Request; // <-- TAMBAHKAN Request
use Illuminate\View\View;
use Illuminate\Http\JsonResponse; // <-- TAMBAHKAN JsonResponse
use Illuminate\Support\Facades\Auth;

class GateLogController extends Controller
{
    /**
     * Menampilkan daftar log gate HANYA untuk truk milik user yang login.
     * Merespons HTML (Load Awal) atau JSON (AJAX Polling).
     */
    public function index(Request $request): View|JsonResponse
    {
        // 1. Ambil ID truk milik user
        $myTruckIds = Auth::user()->trucks()->pluck('id');

        // 2. Query dasar
        $query = GateLog::whereIn('truck_id', $myTruckIds)
                       ->with('truck') // Eager load truck
                       ->latest();

        // 3. Cek jika ini request AJAX (polling)
        if ($request->ajax()) {
            // Ambil data terbaru + paginasi
            $logs = $query->paginate(15)->withQueryString(); 
            
            return response()->json([
                'data' => $logs->items(),
                'pagination' => (string) $logs->links('vendor.pagination.bootstrap-4')
            ]);
        }

        // 4. Jika request normal (Load Halaman)
        $logs = $query->paginate(15);
        
        return view('member.gate_logs.index', compact('logs'));
    }
}