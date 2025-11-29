<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
        // ---------------------------------
    }
}
