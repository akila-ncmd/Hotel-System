<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\CancelNoCreditReservations::class,
        \App\Console\Commands\ProcessNoShowsAndReport::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('reservations:cancel-no-credit')->dailyAt('18:59');
        $schedule->command('reservations:process-no-shows-and-report')->dailyAt('19:00');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}