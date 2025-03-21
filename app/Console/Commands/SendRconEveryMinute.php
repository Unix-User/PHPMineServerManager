<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SendRconEveryMinute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minecraft:send-rcon-every-minute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia um comando Rcon a cada minuto';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $command = 'list';

        $request = new Request(['command' => $command]);
        $onlinePlayers = app('App\Http\Controllers\MinecraftRconController')->executeInternalCommand($request);

        $command = 'lp group jogador listmembers';

        $request = new Request(['command' => $command]);
        $groupMembers = app('App\Http\Controllers\MinecraftRconController')->executeInternalCommand($request);
        Log::channel('single')->info($groupMembers);
        if (is_array($onlinePlayers)) {
            Log::channel('single')->info( 'array found');
            foreach ($onlinePlayers as $player) {
                if (is_string($player) && in_array($player, $groupMembers)) {
                    Log::channel('single')->info('Fixing permissions for: ' . $player);
                    $command = 'lp user ' . $player . ' parent remove default';

                    $request = new Request(['command' => $command]);
                    app('App\Http\Controllers\MinecraftRconController')->executeInternalCommand($request);
                }
            }
        }
    }
}
