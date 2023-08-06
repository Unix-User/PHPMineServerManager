<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MinecraftController extends Controller
{
    protected function runUpdate($command, $message)
    {
        Artisan::call($command);

        return response()->json(['message' => $message]);
    }

    public function runPoiUpdate()
    {
        return $this->runUpdate('minecraft:poi-update', 'POI atualizado com sucesso.');
    }

    public function runMapUpdate()
    {
        return $this->runUpdate('minecraft:map-update', 'Mapa atualizado com sucesso.');
    }
}

