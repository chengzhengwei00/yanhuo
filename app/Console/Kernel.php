<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ScheduleUpdate;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //\App\Console\Commands\ScheduleEmail::class,
        //\App\Console\Commands\SetTrackAll::class,
        \App\Console\Commands\GetManageList::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

       // $schedule->command('schedule:email')->everyMinute()->withoutOverlapping();
        //$schedule->command('set_track_all')->everyMinute()->withoutOverlapping();
        $schedule->command('get_manage_list')->everyMinute()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
