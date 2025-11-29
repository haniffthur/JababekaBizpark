<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GateLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse; // <-- Pastikan ini di-import

class GateManagementController extends Controller
{
    /**
     * Menampilkan daftar semua log aktivitas gerbang (Load Halaman).
     */
    public function index(Request $request): View
    {
        // Query dasar
        $query = GateLog::with(['truck.user', 'user']);

        // Filter (jika ada)
        if ($request->filled('status')) {
            $query->where('status', 'like', '%' . $request->status . '%');
        }
        if ($request->filled('license_plate')) {
            $query->where(function($q) use ($request) {
                $q->where('license_plate', 'like', '%' . $request->license_plate . '%')
                  ->orWhereHas('truck', function ($subQ) use ($request) {
                      $subQ->where('license_plate', 'like', '%' . $request->license_plate . '%');
                  });
            });
        }
        
        // Data untuk load halaman pertama (dengan paginasi)
        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('admin.gate_logs.index', [
            'logs' => $logs,
            'filters' => $request->only(['status', 'license_plate'])
        ]);
    }
    
    /**
     * Mengambil data log untuk AJAX polling (JSON).
     * Rute: GET admin/gate-logs/data
     */
    public function getLogDataJson(Request $request): JsonResponse
    {
        // Query dasar
        $query = GateLog::with(['truck.user', 'user']); // Load kedua relasi

        // Filter (jika ada)
        if ($request->filled('status')) {
            $query->where('status', 'like', '%' . $request->status . '%');
        }
        if ($request->filled('license_plate')) {
            $query->where(function($q) use ($request) {
                $q->where('license_plate', 'like', '%' . $request->license_plate . '%')
                  ->orWhereHas('truck', function ($subQ) use ($request) {
                      $subQ->where('license_plate', 'like', '%' . $request->license_plate . '%');
                  });
            });
        }
        
        // Ambil data untuk AJAX (dengan paginasi)
        $logs = $query->latest()->paginate(20);

        return response()->json([
            'data' => $logs->items(),
            'pagination' => (string) $logs->links('vendor.pagination.bootstrap-4')
        ]);
    }
}