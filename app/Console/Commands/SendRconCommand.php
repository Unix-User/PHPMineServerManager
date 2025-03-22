<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;

class SendRconCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minecraft:send-rcon-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia o comando list';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $commands = [
            // 'execute as @a[tag=hasMoreThan32NetheriteBars] at @s run tell @s Voce foi amaldiçoado pela netherite',
            // 'execute as @a[tag=hasMoreThan32Diamonds] at @s run tell @s Voce foi amaldiçoado pelos diamantes',
            'execute as @a[tag=hasShulkerBoxes] run tell @s Você está com a maldição do shulker!',
            'execute as @a[tag=hasShulkerBoxes] at @s run summon minecraft:piglin ~ ~ ~',
            'execute as @a[tag=hasShulkerBoxes] at @s run summon minecraft:piglin ~ ~ ~',
            'execute as @a[tag=hasShulkerBoxes] at @s run summon minecraft:piglin ~ ~ ~',
            'execute as @a[tag=hasShulkerBoxes] at @s run summon minecraft:piglin ~ ~ ~',
            'execute as @a[tag=hasShulkerBoxes] at @s run summon minecraft:piglin ~ ~ ~',

            'execute as @a[tag=hasDiamondBlocks] run tell @s Você está com a maldição do diamante!',
            'execute as @a[tag=hasDiamondBlocks] at @s run summon minecraft:spider ~ ~ ~ {Passengers:[{id:"minecraft:skeleton",HandItems:[{id:"minecraft:bow",Count:1b}]}]}',
            'execute as @a[tag=hasDiamondBlocks] at @s run summon minecraft:skeleton ~ ~ ~',
            'execute as @a[tag=hasDiamondBlocks] at @s run summon minecraft:skeleton ~ ~ ~',
            'execute as @a[tag=hasDiamondBlocks] at @s run summon minecraft:skeleton ~ ~ ~',
            'execute as @a[tag=hasDiamondBlocks] at @s run summon minecraft:skeleton ~ ~ ~',

            'execute as @a[tag=hasNetheriteBlocks] run tell @s Você está com a maldição da netherite!',
            'execute as @a[tag=hasNetheriteBlocks] at @s run summon minecraft:wither_skeleton ~ ~ ~',
            'execute as @a[tag=hasNetheriteBlocks] at @s run summon minecraft:wither_skeleton ~ ~ ~',
            'execute as @a[tag=hasNetheriteBlocks] at @s run summon minecraft:wither_skeleton ~ ~ ~',
            'execute as @a[tag=hasNetheriteBlocks] at @s run summon minecraft:wither_skeleton ~ ~ ~',
            'execute as @a[tag=hasNetheriteBlocks] at @s run summon minecraft:wither_skeleton ~ ~ ~',

            // 'execute as @a[tag=hasMoreThan32Diamonds] at @s run summon minecraft:witch ~ ~ ~ {Count:10}',
            // 'execute as @a[tag=hasMoreThan32NetheriteBars] at @s run summon minecraft:wither_skeleton ~ ~ ~ {Count:10}',

        ];

        foreach ($commands as $command) {
            $request = new Request(['command' => $command]);
            app('App\Http\Controllers\MinecraftRconController')->executeInternalCommand($request);
        }
    }
}