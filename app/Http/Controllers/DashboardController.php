<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse; // Penting untuk AJAX
use App\Models\User;
use App\Models\Truck;
use App\Models\Billing;
use App\Models\GateLog;
use App\Models\QrCode;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Tampilkan view utama dashboard (Hanya Merender HTML).
     * Rute: GET /
     */
   public function index(): View
    {
        $user = Auth::user();

        // Variabel Stats Kosong untuk mencegah error "Undefined variable $stats" saat initial load
        $emptyStats = [
            'total_members' => 0,
            'trucks_inside' => 0,
            'pending_billings' => 0,
            'logs_today' => 0,
            'total_trucks' => 0, // <-- TAMBAHKAN INI
    'active_qrs' => 0,
        ];
        
        if ($user->isAdmin()) {
            // Data Chart untuk Admin (7 hari terakhir)
            $logActivity = GateLog::select(
                                DB::raw('DATE(created_at) as date'), 
                                DB::raw("SUM(CASE WHEN status = 'Berhasil Masuk' THEN 1 ELSE 0 END) as check_ins"),
                                DB::raw("SUM(CASE WHEN status = 'Berhasil Keluar' THEN 1 ELSE 0 END) as check_outs")
                            )
                            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                            ->groupBy('date')
                            ->orderBy('date', 'asc')
                            ->get();
            
            $chartLabels = $logActivity->pluck('date')->map(fn($date) => Carbon::parse($date)->format('d M'));
            $chartCheckIns = $logActivity->pluck('check_ins');
            $chartCheckOuts = $logActivity->pluck('check_outs');
            
            // Log terbaru (5 baris pertama)
         // Tambahkan 'user' di array with()
$recentLogs = GateLog::with(['truck.user', 'user'])->latest()->take(5)->get();

            return view('admin.dashboard.index', compact('chartLabels', 'chartCheckIns', 'chartCheckOuts', 'emptyStats', 'recentLogs'));

        } else {
            // Data Chart untuk Member
            $totalTrucks = $user->trucks()->count();
            $trucksInside = Truck::where('user_id', $user->id)->where('is_inside', true)->count();
            $trucksOutside = $totalTrucks - $trucksInside;
            $pieChartData = [$trucksInside, $trucksOutside];
            
            // Status truk (5 baris pertama)
            $myTruckStatus = $user->trucks()->latest()->take(5)->get();

            return view('member.dashboard.index', compact('pieChartData', 'emptyStats', 'myTruckStatus'));
        }
    }

    /**
     * Mengambil data statistik dan log terbaru untuk AJAX (ADMIN).
     * Rute: GET admin/data/stats
     */
    public function getAdminData(): JsonResponse
    {
        // 1. Info Cards Stats
        $stats = [
            'total_members' => User::where('role', 'member')->count(),
            'trucks_inside' => Truck::where('is_inside', true)->count(),
            'pending_billings' => Billing::where('status', 'pending')->sum('total_amount'),
            'logs_today' => GateLog::whereDate('created_at', today())->count(),
        ];

        // 2. Log terbaru (hanya 5)
        $recentLogs = GateLog::with('truck.user','user')->latest()->take(5)->get();

        return response()->json([
            'stats' => $stats,
            'recentLogs' => $recentLogs
        ]);
    }

    /**
     * Mengambil data status dan tagihan untuk AJAX (MEMBER).
     * Rute: GET member/data/stats
     */
    public function getMemberData(): JsonResponse
    {
        $user = Auth::user();
        $myTruckIds = $user->trucks()->pluck('id');

        // 1. Info Cards Stats
        $stats = [
            'total_trucks' => $myTruckIds->count(),
            'trucks_inside' => Truck::whereIn('id', $myTruckIds)->where('is_inside', true)->count(),
            'active_qrs' => QrCode::whereIn('truck_id', $myTruckIds)->where('status', 'aktif')->count(),
            'pending_billings' => $user->billings()->where('status', 'pending')->sum('total_amount'),
        ];
        
        // 2. Tabel Status Truk (untuk di-render di JS)
        $myTruckStatus = $user->trucks()->latest()->take(5)->get();

        return response()->json([
            'stats' => $stats,
            'myTruckStatus' => $myTruckStatus
        ]);
    }
    
}