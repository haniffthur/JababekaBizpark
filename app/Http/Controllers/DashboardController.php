<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Truck;
use App\Models\Billing;
use App\Models\GateLog;
use App\Models\QrCode;
use App\Models\PersonalQr;
use App\Models\IplBill;
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

        // Variabel Stats Kosong untuk mencegah error saat initial load
        $emptyStats = [
            'total_traffic' => 0,
            'vehicles_inside' => 0,
            'revenue_month' => 0,
            'total_members' => 0,
            'total_trucks' => 0,
            'active_qrs' => 0,
            'pending_qr_count' => 0,
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
            $recentLogs = GateLog::with(['truck.user', 'user'])->latest()->take(5)->get();

            // TAMBAHAN: Hitung pending QR untuk sidebar
            $pendingQrCount = QrCode::where('is_approved', false)
                                    ->where('status', 'baru')
                                    ->count();

            return view('admin.dashboard.index', compact('chartLabels', 'chartCheckIns', 'chartCheckOuts', 'emptyStats', 'recentLogs', 'pendingQrCount'));

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
        // 1. Hitung Traffic Hari Ini
        $logsToday = GateLog::whereDate('created_at', today())->count();
        
        // 2. Hitung Revenue Bulan Ini
        $revenue = IplBill::where('period', Carbon::now()->format('F Y'))->sum('amount');
        
        // 3. Hitung Kendaraan di Dalam (Truk + Pribadi)
        $trucksInside = Truck::where('is_inside', true)->count();
        $personalInside = PersonalQr::where('status', 'aktif')->count();
        $totalInside = $trucksInside + $personalInside;
        
        // 4. Total Members
        $totalMembers = User::where('role', 'member')->count();

        // 5. Pending QR Count
        $pendingQrCount = QrCode::where('is_approved', false)
                                ->where('status', 'baru')
                                ->count();

        $stats = [
            'total_traffic' => $logsToday,
            'revenue_month' => $revenue,
            'vehicles_inside' => $totalInside,
            'total_members' => $totalMembers,
            'pending_qr_count' => $pendingQrCount,
        ];

        // Recent Logs
        $recentLogs = GateLog::with(['truck.user', 'user'])->latest()->take(5)->get();

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
        
        // 2. Tabel Status Truk
        $myTruckStatus = $user->trucks()->latest()->take(5)->get();

        return response()->json([
            'stats' => $stats,
            'myTruckStatus' => $myTruckStatus
        ]);
    }

    public function getChartData(Request $request): JsonResponse
    {
        $period = $request->query('period', 'week'); 
        $startDate = now();
        $endDate = now();
        $groupBy = 'date';
        
        // Tentukan Range Waktu
        if ($period == 'today') {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
            $groupBy = 'hour';
        } elseif ($period == 'week') {
            $startDate = now()->subDays(6)->startOfDay();
            $endDate = now()->endOfDay();
        } elseif ($period == 'month') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        } elseif ($period == 'year') {
            $startDate = now()->startOfYear();
            $endDate = now()->endOfYear();
            $groupBy = 'month';
        } elseif ($period == 'custom') {
            $startDate = Carbon::parse($request->query('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->query('end_date'))->endOfDay();
            
            if ($startDate->diffInDays($endDate) > 30) {
                $groupBy = 'month';
            }
        }

        // Query Builder
        $query = GateLog::whereBetween('created_at', [$startDate, $endDate]);

        // Select berdasarkan grouping
        if ($groupBy == 'hour') {
            $query->select(
                DB::raw('HOUR(created_at) as time_unit'),
                DB::raw("SUM(CASE WHEN status LIKE '%Berhasil Masuk%' THEN 1 ELSE 0 END) as check_ins"),
                DB::raw("SUM(CASE WHEN status LIKE '%Berhasil Keluar%' THEN 1 ELSE 0 END) as check_outs")
            )->groupBy('time_unit');
        } elseif ($groupBy == 'month') {
            $query->select(
                DB::raw('MONTH(created_at) as time_unit'),
                DB::raw("SUM(CASE WHEN status LIKE '%Berhasil Masuk%' THEN 1 ELSE 0 END) as check_ins"),
                DB::raw("SUM(CASE WHEN status LIKE '%Berhasil Keluar%' THEN 1 ELSE 0 END) as check_outs")
            )->groupBy('time_unit');
        } else {
            $query->select(
                DB::raw('DATE(created_at) as time_unit'),
                DB::raw("SUM(CASE WHEN status LIKE '%Berhasil Masuk%' THEN 1 ELSE 0 END) as check_ins"),
                DB::raw("SUM(CASE WHEN status LIKE '%Berhasil Keluar%' THEN 1 ELSE 0 END) as check_outs")
            )->groupBy('time_unit');
        }
        
        $logs = $query->get();

        // Formatting Data
        $labels = [];
        $dataCheckIn = [];
        $dataCheckOut = [];

        if ($groupBy == 'hour') {
            for ($i = 0; $i <= 23; $i++) {
                $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
                $found = $logs->where('time_unit', $i)->first();
                $dataCheckIn[] = $found->check_ins ?? 0;
                $dataCheckOut[] = $found->check_outs ?? 0;
            }
        } elseif ($period == 'week' || $period == 'month') {
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $labels[] = $current->format('d M');
                $dateStr = $current->format('Y-m-d');
                $found = $logs->where('time_unit', $dateStr)->first();
                $dataCheckIn[] = $found->check_ins ?? 0;
                $dataCheckOut[] = $found->check_outs ?? 0;
                $current->addDay();
            }
        } elseif ($groupBy == 'month') {
            for ($i = 1; $i <= 12; $i++) {
                $labels[] = date('M', mktime(0, 0, 0, $i, 1));
                $found = $logs->where('time_unit', $i)->first();
                $dataCheckIn[] = $found->check_ins ?? 0;
                $dataCheckOut[] = $found->check_outs ?? 0;
            }
        }

        return response()->json([
            'labels' => $labels,
            'checkIns' => $dataCheckIn,
            'checkOuts' => $dataCheckOut
        ]);
    }
 public function checkPendingQr(): JsonResponse
    {
        $truckPending = \App\Models\QrCode::where('is_approved', false)
                                    ->where('status', 'baru')
                                    ->count();
        
        $personalPending = \App\Models\PersonalQr::where('is_approved', false)->count();

        return response()->json([
            'truck_count' => $truckPending,
            'personal_count' => $personalPending,
            'total' => $truckPending + $personalPending
        ]);
    }
}