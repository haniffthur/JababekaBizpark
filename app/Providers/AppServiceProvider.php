<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View; // <-- 1. TAMBAHKAN IMPORT INI
use App\Models\QrCode;
use App\Models\PersonalQr;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('QrCode', function ($expression) {
            // Kita pakai library QR Code bawaan (misal: simplesoftwareio/simple-qrcode)
            // Pastikan kamu sudah menginstal ini: composer require simplesoftwareio/simple-qrcode
            // Jika belum, instal dulu.
            return "<?php echo QrCode::size(150)->generate($expression); ?>";
        });
       View::composer('layouts.partials._admin_sidebar', function ($view) {
        
        // 1. Hitung Pending QR TRUK
        $pendingQrCount = QrCode::where('is_approved', false)
                                ->where('status', 'baru')
                                ->count();
        
        // 2. Hitung Pending QR PRIBADI (BARU)
        $pendingPersonalQrCount = PersonalQr::where('is_approved', false)->count();
        
        // Kirim kedua variabel ke view
        $view->with('pendingQrCount', $pendingQrCount);
        $view->with('pendingPersonalQrCount', $pendingPersonalQrCount);
    });
        // ---------------------------------
    }
}
