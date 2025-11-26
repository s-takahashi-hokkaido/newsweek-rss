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
        // RSS取得（5分ごと）
        $schedule->command('rss:fetch')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/rss-fetch.log'));

        // 古い記事の削除（毎日午前3時）
        $schedule->command('articles:cleanup')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/cleanup.log'));

        // 古いログの削除（毎日午前3時10分）
        $schedule->command('logs:cleanup')
            ->dailyAt('03:10')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/cleanup.log'));
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
