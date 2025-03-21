<?php

namespace App\Console\Commands;

use App\Jobs\POIUpdateJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
        Log::info('Tarefa de atualização dos POIs enfileirada com sucesso.');
        POIUpdateJob::dispatch();
    }
}