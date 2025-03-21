<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class POIUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $paths = [
            'overviewer' => 'C:\Projetos\minecraft-server\overviewer-v1.20.3\overviewer.exe',
            'config' => 'C:\Projetos\minecraft-server\config.conf',
            'workingDir' => 'C:\Projetos\minecraft-server'
        ];

        $command = "\"{$paths['overviewer']}\" --genpoi -c \"{$paths['config']}\"";
        chdir($paths['workingDir']);
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        $logMessage = $return_var != 0 ?
            'Erro ao atualizar POIs: ' . implode("\n", $output) :
            'POIs atualizados com sucesso';
        Log::channel('single')->{$return_var != 0 ? 'error' : 'info'}($logMessage);
    }
}