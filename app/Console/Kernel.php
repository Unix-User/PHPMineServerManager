<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CheckDiscordChat;
use App\Console\Commands\AnswerDiscordMessages;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        CheckDiscordChat::class,
        AnswerDiscordMessages::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('discord:answer-messages')
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('vip:check-activation')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('minecraft:check-chat')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('discord:check-messages')
            ->fiveMinute()
            ->withoutOverlapping()
            ->onOneServer();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
