<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Thedudeguy\Rcon;

class MinecraftRconController extends Controller
{
    private $rcon;
    private $isConnected = false;

    public function __construct()
    {
        $this->rcon = new Rcon(env('RCON_SERVER', '127.0.0.1'), env('RCON_PORT', 25575), env('RCON_PASSWORD', 'Dracar2s'), 3);

    }

    public function executeCommand(Request $request)
    {
        $validatedData = $this->validateRequest($request);

        if (!$this->isConnected) {
            $this->isConnected = $this->rcon->connect();
        }

        return $this->sendCommandToServer($validatedData['command']);
    }

    public function accessRconTerminal(Request $request)
    {
        $validatedData = $this->validateRequest($request);

        if (!$this->isConnected) {
            $this->isConnected = $this->rcon->connect();
        }

        return $this->sendCommandToServer($validatedData['command']);
    }

    private function validateRequest(Request $request)
    {
        return $request->validate([
            'command' => 'required|string',
        ]);
    }

    private function sendCommandToServer($command)
    {
        if (!$this->isConnected) {
            $this->isConnected = $this->rcon->connect();
        }

        try {
            $response = $this->rcon->sendCommand($command);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['response' => 'Command execution failed'], 500);
        }

        if ($response === false) {
            return response()->json(['response' => 'Command execution failed'], 500);
        }

        return response()->json(['response' => $response]);
    }

    public function closeRconConnection()
    {
        if ($this->isConnected) {
            $this->rcon->disconnect();
            $this->isConnected = false;
        }

        return response()->json(['message' => 'RCON connection closed']);
    }
}

