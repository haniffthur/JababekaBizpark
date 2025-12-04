<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Di sinilah Anda dapat mendaftarkan rute web untuk aplikasi Anda.
|
*/

// --- Import Semua Controller ---
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

// Admin Controllers
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\BillingController as AdminBillingController;
use App\Http\Controllers\Admin\GateManagementController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AdminQrController; // <-- Controller Baru
use App\Http\Controllers\Admin\GateMachineController;

// Member Controllers
use App\Http\Controllers\Member\TruckController;
use App\Http\Controllers\Member\QrCodeController;
use App\Http\Controllers\Member\BillingController as MemberBillingController;
use App\Http\Controllers\Member\GateLogController as MemberGateLogController;
use App\Http\Controllers\Admin\PersonalQrController as AdminPersonalQrController;
use App\Http\Controllers\Member\PersonalQrController as MemberPersonalQrController;
use App\Http\Controllers\Member\IplBillController;

 Route::get('logout', [AuthController::class, 'logout'])->name('logout');
// --- RUTE UNTUK UMUM (GUEST) ---
Route::middleware(['guest'])->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout',[AuthController::class,'logout'])->name('logout');
    
});

// --- RUTE YANG PERLU LOGIN (SEMUA ROLE) ---
Route::middleware(['auth'])->group(function () {
   
    
    // Rute 'Dashboard' akan diarahkan berdasarkan role di controller
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});


// --- GRUP KHUSUS ADMIN ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Manajemen Member (CRUD)
    Route::resource('members', MemberController::class);
    Route::resource('personal-qrs', AdminPersonalQrController::class);
    Route::get('members/data', [MemberController::class, 'getMemberData'])->name('members.data');

    // Manajemen Keuangan (CRUD)
    Route::resource('billings', AdminBillingController::class);

    // Laporan & Log
    Route::get('gate-logs', [GateManagementController::class, 'index'])->name('gate.logs');
    Route::get('gate-logs/data', [GateManagementController::class, 'getLogDataJson'])->name('gate.logs.data'); // AJAX

    // Pengaturan Sistem
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');

    // Rute AJAX Dashboard
    Route::get('data/stats', [DashboardController::class, 'getAdminData'])->name('data.stats');
    Route::get('dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('chart.data');

    // ** BARU: Persetujuan QR Code **
    Route::get('qr-approvals', [AdminQrController::class, 'index'])->name('qr.approvals.index');
    Route::post('qr-approvals/{qrcode}/approve', [AdminQrController::class, 'approve'])->name('qr.approvals.approve');

    Route::resource('gate-machines', GateMachineController::class);
    Route::get('personal-qrs/member/{user}', [AdminPersonalQrController::class, 'showMemberQrs'])->name('personal-qrs.member');
});


// --- GRUP KHUSUS MEMBER ---
Route::middleware(['auth', 'role:member'])->prefix('member')->name('member.')->group(function () {
    
    // Manajemen Truk (CRUD)
    Route::resource('trucks', TruckController::class);
    Route::get('personal-qrs', [MemberPersonalQrController::class, 'index'])->name('personal_qrs.index');
    
    // Manajemen QR Code
    Route::get('qrcodes', [QrCodeController::class, 'index'])->name('qrcodes.index');
    Route::get('qrcodes/create', [QrCodeController::class, 'create'])->name('qrcodes.create');
    Route::post('qrcodes', [QrCodeController::class, 'store'])->name('qrcodes.store');
    Route::get('qrcodes/{qrcode}', [QrCodeController::class, 'show'])->name('qrcodes.show');
    Route::delete('qrcodes/{qrcode}', [QrCodeController::class, 'destroy'])->name('qrcodes.destroy');
    Route::get('qrcodes/{qrcode}/download', [QrCodeController::class, 'downloadPDF'])->name('qrcodes.download');
    Route::get('personal-qrs/{qrcode}/print', [MemberPersonalQrController::class, 'printQr'])->name('personal_qrs.print');
    Route::get('personal-qrs/{qrcode}/download', [MemberPersonalQrController::class, 'downloadPDF'])->name('personal_qrs.download');

    // Tagihan Saya
    Route::get('billings', [MemberBillingController::class, 'index'])->name('billings.index');
    Route::get('billings/{billing}', [MemberBillingController::class, 'show'])->name('billings.show');

    // Histori Truk Saya
    Route::get('gate-logs', [MemberGateLogController::class, 'index'])->name('gate.logs');

    // Rute AJAX Dashboard
    Route::get('data/stats', [DashboardController::class, 'getMemberData'])->name('data.stats');

    Route::get('ipl-bills', [IplBillController::class, 'index'])->name('ipl.index');
Route::post('ipl-bills/{iplBill}/pay', [IplBillController::class, 'pay'])->name('ipl.pay');
});