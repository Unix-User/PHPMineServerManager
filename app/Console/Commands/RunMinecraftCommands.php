<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunMinecraftCommands extends Command
{
    protected $signature = 'minecraft:run-commands';
    protected $description = 'Run Minecraft commands';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $overviewerPath = 'C:\Projetos\minecraft-server\overviewer-v1.20.1\overviewer.exe';
        $configPath = 'C:\Projetos\minecraft-server\config.conf';

        // Run first command
        $command1 = "\"$overviewerPath\" --genpoi -c \"$configPath\"";
        exec($command1);

        // Run second command
        $command2 = "\"$overviewerPath\" -c \"$configPath\"";
        exec($command2);

        $this->info('Minecraft commands executed successfully.');
    }
}
