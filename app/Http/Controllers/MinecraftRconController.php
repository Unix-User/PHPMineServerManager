<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Thedudeguy\Rcon;


class MinecraftRconController extends Controller
{
    private $rcon;

    public function __construct()
    {
    }

    public function executeCommand(Request $request)
    {
        $validatedData = $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'password' => 'required|string',
            'command' => 'required|string',
        ]);

        $this->rcon = new Rcon($validatedData['host'], $validatedData['port'], $validatedData['password'], 3);

        if (!$this->rcon->connect()) {
            return response()->json(['response' => 'Connection failed'], 500);
        }

        try {
            $response = $this->rcon->sendCommand($validatedData['command']);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['response' => 'Command execution failed'], 500);
        }

        $this->rcon->disconnect();

        if ($response === false) {
            return response()->json(['response' => 'Command execution failed'], 500);
        }

        return response()->json(['response' => $response]);
    }
}
