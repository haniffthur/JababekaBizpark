<?php

// app/Providers/AuthServiceProvider.php

namespace App\Providers;

// Import model-model yang kita butuhkan
use App\Models\Truck;
use App\Models\QrCode;
use App\Models\Billing;
use App\Models\User;
use Illuminate\Support\Facades\Gate; // Import Gate
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        
        // --- DEFINISIKAN GATES KITA DI SINI ---

        /**
         * Gate untuk mengecek apakah user boleh mengelola (edit/delete/view) Truk ini.
         * Aturan: Boleh jika dia ADMIN, ATAU jika dia adalah PEMILIK truk tsb.
         */
        Gate::define('manage-truck', function (User $user, Truck $truck) {
            return $user->role === 'admin' || $user->id === $truck->user_id;
        });

        /**
         * Gate untuk mengecek apakah user boleh mengelola QR Code ini.
         * Aturan: Boleh jika dia ADMIN, ATAU jika dia adalah PEMILIK dari truk 
         * yang terhubung ke QR code ini.
         */
        Gate::define('manage-qrcode', function (User $user, QrCode $qrCode) {
            if ($user->role === 'admin') {
                return true;
            }
            // Kita cek kepemilikan melalui relasi: qrCode -> truck -> user
            return $user->id === $qrCode->truck->user_id;
        });

        /**
         * Gate untuk mengecek apakah user boleh melihat Tagihan (Billing) ini.
         * Aturan: Boleh jika dia ADMIN, ATAU jika dia adalah PEMILIK tagihan tsb.
         */
        Gate::define('view-billing', function (User $user, Billing $billing) {
            return $user->role === 'admin' || $user->id === $billing->user_id;
        });
    }
}
