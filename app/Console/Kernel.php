<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('ipl:generate')
                 ->monthlyOn(1, '00:00')
                 ->timezone('Asia/Jakarta') // Penting agar sesuai jam Indonesia
                 ->appendOutputTo(storage_path('logs/scheduler.log')); // Catat log biar ketahuan jalan/enggak
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
