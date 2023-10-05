<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;

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
        $command = [
            'execute as @a[tag=hasNetheriteBlocks] at @s run summon minecraft:skeleton ~ ~ ~ {Count:10}',
            'execute as @a[tag=hasDiamondBlocks] at @s run summon minecraft:zombie_villager ~ ~ ~ {Count:10}',
            'execute as @a[tag=hasMoreThan32Diamonds] at @s run summon minecraft:witch ~ ~ ~ {Count:10}',
            'execute as @a[tag=hasMoreThan32NetheriteBars] at @s run summon minecraft:wither_skeleton ~ ~ ~ {Count:10}',
            'execute as @a[tag=hasMoreThan32NetheriteBars] at @s run tell @s Voce foi amaldiçoado pela netherite',
            'execute as @a[tag=hasMoreThan32Diamonds] at @s run tell @s Voce foi amaldiçoado pelos diamantes',
            'execute as @a[tag=hasDiamondBlocks] run tell @s Você está com a maldição do diamante!',
            'execute as @a[tag=hasNetheriteBlocks] run tell @s Você está com a maldição da netherite!',
        ];

        $request = new Request(['command' => $command]);
        app('App\Http\Controllers\MinecraftRconController')->executeInternalCommand($request);
    }
}
