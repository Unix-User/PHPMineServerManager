<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OverviewerPoiRender extends Command
{
    protected $signature = 'minecraft:poi-update';
    protected $description = 'Atualiza POIs usando overviewer';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $overviewerPath = 'C:\Projetos\minecraft-server\overviewer-v1.20.1\overviewer.exe';
        $configPath = 'C:\Projetos\minecraft-server\config.conf';
        $workingDirPath = 'C:\Projetos\minecraft-server';

        // Run first command
        $command = "\"$overviewerPath\" --genpoi -c \"$configPath\"";
        chdir($workingDirPath);
        exec($command);

        $this->info('Minecraft commands executed successfully.');
    }
}