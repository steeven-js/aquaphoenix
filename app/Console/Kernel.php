<?php

namespace App\Console;

use App\Console\Commands\InitializeStats;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Les commandes à enregistrer dans le kernel.
     *
     * @var array
     */
    protected $commands = [
        InitializeStats::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * These should be classes that implement the ShouldQueue interface.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('stats:initialize')
            ->everySixHours();
    }

    /**
     * Register the commands for your application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
