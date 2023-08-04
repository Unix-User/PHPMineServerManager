<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MinecraftController extends Controller
{
    public function runCommands()
    {
        Artisan::call('minecraft:run-commands');

        return response()->json(['message' => 'Comandos Minecraft executados com sucesso.']);
    }
}
