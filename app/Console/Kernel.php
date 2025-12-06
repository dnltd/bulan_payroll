<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the application's commands.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // This will run every Friday at 11:00 PM
        $schedule->command('payroll:generate')->fridays()->at('23:00');
    }

    /**
     * Bootstrap any application services.
     */
    protected function bootstrap()
    {
        parent::bootstrap();

        // Ensures time zone is consistent
        date_default_timezone_set(config('app.timezone'));
    }
}
