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
        if (app()->environment('local')) {
            $schedule->command('app:revisar-ordenes-pago-linea')->everyFiveMinutes();
        } else {
            $schedule->command('app:revisar-ordenes-pago-linea')->dailyAt('00:00');
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected $commands = [
        \App\Console\Commands\RevisarOrdenesPagoLinea::class,
    ];
}
