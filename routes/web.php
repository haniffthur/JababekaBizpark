<?php

use Illuminate\Support\Facades\Route;

// --- Import Semua Controller ---
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// Admin Controllers
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\BillingController as AdminBillingController;
use App\Http\Controllers\Admin\GateManagementController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AdminQrController; 
use App\Http\Controllers\Admin\GateMachineController;
use App\Http\Controllers\Admin\PersonalQrController as AdminPersonalQrController;


// Member Controllers
use App\Http\Controllers\Member\TruckController;
use App\Http\Controllers\Member\QrCodeController;
use App\Http\Controllers\Member\BillingController as MemberBillingController;
use App\Http\Controllers\Member\GateLogController as MemberGateLogController;
use App\Http\Controllers\Member\PersonalQrController as MemberPersonalQrController;
use App\Http\Controllers\Member\IplBillController;



/*
|--------------------------------------------------------------------------
| Rute Umum (Guest)
|--------------------------------------------------------------------------
*/
Route::middleware(['guest'])->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Rute Terautentikasi (Admin & Member)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('logout', [AuthController::class, 'logout']); // Fallback GET logout

    // Dashboard Utama (Redirect based on role)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});


/*
|--------------------------------------------------------------------------
| GRUP ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // --- 1. MANAJEMEN DATA ---
    Route::resource('members', MemberController::class);
    Route::get('members/data', [MemberController::class, 'getMemberData'])->name('members.data'); // AJAX

    // --- 2. OPERASIONAL (KEUANGAN & QR TRUK) ---
    // Billing (Keuangan)
    Route::resource('billings', AdminBillingController::class);
    Route::post('billings/{billing}/approve', [AdminBillingController::class, 'approve'])->name('billings.approve');
    Route::post('billings/{billing}/reject', [AdminBillingController::class, 'reject'])->name('billings.reject');

    // Persetujuan QR Truk
    Route::get('qr-approvals', [AdminQrController::class, 'index'])->name('qr.approvals.index');
    Route::post('qr-approvals/{qrcode}/approve', [AdminQrController::class, 'approve'])->name('qr.approvals.approve');

    // Laporan / Log Sistem
    Route::get('gate-logs', [GateManagementController::class, 'index'])->name('gate.logs');
    Route::get('gate-logs/data', [GateManagementController::class, 'getLogDataJson'])->name('gate.logs.data'); // AJAX

    // --- 3. PENGATURAN (SISTEM, MESIN, QR PRIBADI) ---
    
    // Pengaturan Sistem Umum
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');

    // Manajemen Mesin Gate (IoT)
    Route::resource('gate-machines', GateMachineController::class);

    // Manajemen & Persetujuan QR Pribadi
    // (Urutan Penting: Route spesifik harus sebelum Resource)
    Route::get('personal-qrs/approvals', [AdminPersonalQrController::class, 'approvals'])->name('personal-qrs.approvals');
   Route::post('personal-qrs/{personalQr}/approve', [AdminPersonalQrController::class, 'approve'])
    ->name('personal-qrs.approve');

// Reject (DELETE)
Route::delete('personal-qrs/{personalQr}/reject', [AdminPersonalQrController::class, 'reject'])
    ->name('personal-qrs.reject');
    Route::get('personal-qrs/member/{user}', [AdminPersonalQrController::class, 'showMemberQrs'])->name('personal-qrs.member');
    
    Route::resource('personal-qrs', AdminPersonalQrController::class)->except(['show']); // Resource umum

    // --- 4. API & AJAX DASHBOARD ---
    Route::get('data/stats', [DashboardController::class, 'getAdminData'])->name('data.stats');
    Route::get('chart/data', [DashboardController::class, 'getChartData'])->name('chart.filter');
    
    // API Ringan untuk Notifikasi Global (Sidebar)
    Route::get('api/check-pending', [DashboardController::class, 'checkPendingQr'])->name('api.check.pending');
   
});


/*
|--------------------------------------------------------------------------
| GRUP MEMBER
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:member'])->prefix('member')->name('member.')->group(function () {
    
    // --- 1. MANAJEMEN ASET ---
    // Truk
    Route::resource('trucks', TruckController::class);
    
    // QR Code Truk
    Route::get('qrcodes', [QrCodeController::class, 'index'])->name('qrcodes.index');
    Route::get('qrcodes/create', [QrCodeController::class, 'create'])->name('qrcodes.create');
    Route::post('qrcodes', [QrCodeController::class, 'store'])->name('qrcodes.store');
    Route::get('qrcodes/{qrcode}', [QrCodeController::class, 'show'])->name('qrcodes.show');
    Route::delete('qrcodes/{qrcode}', [QrCodeController::class, 'destroy'])->name('qrcodes.destroy');
    Route::get('qrcodes/{qrcode}/download', [QrCodeController::class, 'downloadPDF'])->name('qrcodes.download');

    // QR Code Pribadi
    Route::get('personal-qrs', [MemberPersonalQrController::class, 'index'])->name('personal_qrs.index');
    Route::post('personal-qrs', [MemberPersonalQrController::class, 'store'])->name('personal_qrs.store'); // Request Baru
    Route::get('personal-qrs/{qrcode}/print', [MemberPersonalQrController::class, 'printQr'])->name('personal_qrs.print');
    Route::get('personal-qrs/{qrcode}/download', [MemberPersonalQrController::class, 'downloadPDF'])->name('personal_qrs.download');

    // --- 2. LAPORAN & TAGIHAN ---
    // Tagihan Saya (Billings)
    Route::get('billings', [MemberBillingController::class, 'index'])->name('billings.index');
    Route::get('billings/{billing}', [MemberBillingController::class, 'show'])->name('billings.show');
    Route::post('billings/{billing}/pay', [MemberBillingController::class, 'pay'])->name('billings.pay'); // Upload Bukti

    // Histori Truk Saya
    Route::get('gate-logs', [MemberGateLogController::class, 'index'])->name('gate.logs');

    // Tagihan IPL Bulanan (Jika masih dipakai)
    Route::get('ipl-bills', [IplBillController::class, 'index'])->name('ipl.index');
    Route::post('ipl-bills/{iplBill}/pay', [IplBillController::class, 'pay'])->name('ipl.pay');

    // --- 3. AJAX DASHBOARD ---
    Route::get('data/stats', [DashboardController::class, 'getMemberData'])->name('data.stats');
     Route::get('api/check-my-requests', [MemberPersonalQrController::class, 'checkMyRequests'])->name('api.check.my.requests');
});