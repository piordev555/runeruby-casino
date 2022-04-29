<?php

namespace App\Console;

use App\Console\Commands\ProcessTRXPayments;
use App\Console\Commands\Quiz;
use App\Console\Commands\Rain;
use App\Console\Commands\ResetWeeklyBonus;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        $schedule->command(Quiz::class)->everyThirtyMinutes();
        $schedule->command(ResetWeeklyBonus::class)->sundays();
        $schedule->command(ProcessTRXPayments::class)->everyMinute();

        $expression = Cache::get('schedule:expressions:rain');
        if (!$expression) {
            $randomMinute = mt_rand(0, 59);

            $hourRange = range(1, 23);
            shuffle($hourRange);
            $randomHours = array_slice($hourRange, 0, mt_rand(5, 15));

            $expression = $randomMinute.' '.implode(',', $randomHours).' * * *';
            Cache::put('schedule:expressions:rain', $expression, Carbon::now()->endOfDay());
        }
        $schedule->command(Rain::class)->cron($expression);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }

}
