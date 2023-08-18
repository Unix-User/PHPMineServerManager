<?php

namespace App\Http\Controllers;

use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class MinecraftController extends Controller
{
    public function serverRules()
    {
        $markdownFilePath = storage_path('..\resources\markdown\rules.md');
        $rules = file_get_contents($markdownFilePath);
        $parsedRules = Markdown::parse($rules)->toHtml(); // Change this line
        return Inertia::render('Rules', ['rules' => $parsedRules]);
    }

    public function donations()
    {
        $markdownFilePath = storage_path('..\resources\markdown\donations.md');
        $rules = file_get_contents($markdownFilePath);
        $parsedMD = Markdown::parse($rules)->toHtml(); // Change this line
        return Inertia::render('Donations', ['donations' => $parsedMD]);
    }


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
        return $this->runUpdate("minecraft:map-update", 'Mapa atualizado com sucesso.');
    }
}