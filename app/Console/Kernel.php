<?php

namespace App\Console;

use App\Jobs\GenerateBills;
use App\Jobs\ImportCharges;
use App\Jobs\SyncVehicles;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(ImportCharges::class)->everyTenMinutes();

        $schedule->job(SyncVehicles::class)->everyTenMinutes();

        $schedule->job(GenerateBills::class)->everyTenMinutes();

        $schedule->command('queue:prune-batches --unfinished=72 --cancelled=72')->daily();
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
