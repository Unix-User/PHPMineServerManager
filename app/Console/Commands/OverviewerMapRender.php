<?php

namespace App\Console\Commands;

use App\Jobs\MapUpdateJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OverviewerMapRender extends Command
{
    protected $signature = 'minecraft:map-update';
    protected $description = 'Atualiza mapa usando overviewer';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('Tarefa de atualização do mapa enfileirada com sucesso.');
        MapUpdateJob::dispatch();
    }
}