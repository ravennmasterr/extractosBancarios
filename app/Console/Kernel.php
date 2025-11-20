<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Storage;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void {
        //nota de prueba
        $schedule->command("app:update")
            ->environments(["local", "production", "testing"])
            ->hourly()
            ->withoutOverlapping()
            ->appendOutputTo(Storage::disk("logs")->path("schedule.log"))
        ;
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
