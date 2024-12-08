<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DiscordController;
use App\Services\OllamaService;

class CheckDiscordMessages extends Command
{
    protected $signature = 'discord:check-messages';
    protected $description = 'Check for new Discord messages and respond if necessary';

    public function handle(DiscordController $discordController, OllamaService $ollamaService)
    {
        $this->info('Checking for new Discord messages...');
        $discordController->getChannelMessages($ollamaService);
        $this->info('Finished checking messages.');
    }
}