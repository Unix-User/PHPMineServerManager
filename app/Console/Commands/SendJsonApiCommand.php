<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\JsonApiReloadedController;

class SendJsonApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minecraft:send-json-api-command {jsonCommand : O comando completo a ser executado no servidor}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia um comando qualquer para ser executado no servidor Minecraft';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jsonCommand = $this->argument('jsonCommand');
        $request = new Request(['command' => $jsonCommand]);
        app()->make(JsonApiReloadedController::class)->runCommand($request);
        
        $this->info("Comando enviado: {$jsonCommand}");
    }
}