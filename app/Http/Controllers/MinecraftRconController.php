<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Thedudeguy\Rcon;

class MinecraftRconController extends Controller
{
    private $rcon;
    private $isConnected = false;

    public function __construct()
    {
        $host = env('RCON_SERVER', '127.0.0.1');
        $port = env('RCON_PORT', 25575);
        $pass = env('RCON_PASSWORD', null);
        $this->rcon = new Rcon($host, $port, $pass, 3);
    }

    public function executeCommand(Request $request)
    {
        return $this->handleCommand($request);
    }

    private function handleCommand(Request $request)
    {
        if (Auth::user()->roles->pluck('name')->contains('admin')) {
            $validatedData = $this->validateRequest($request);
            if (!$this->isConnected) {
                $this->isConnected = $this->rcon->connect();
            }
            Log::channel('single')->info('User ' . $request->user()->email . ' is sending command: ' . $validatedData['command']);
            Log::channel('single')->info('Connection status: ' . ($this->isConnected ? 'Connected' : 'Not Connected'));
            return $this->sendCommandToServer($validatedData['command']);
        } else {
            abort(403, 'Unauthorized');
        }
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
            Log::channel('single')->error($e);
            return response()->json(['response' => 'Command execution failed'], 500);
        }
        if ($response === false) {
            return response()->json(['response' => 'Command execution failed'], 500);
        }
        Log::channel('single')->info('Command response: ' . $response);
        return response()->json(['response' => $response]);
    }

    public function closeRconConnection()
    {
        if (Auth::user()->roles->pluck('name')->contains('admin')) {
            if ($this->isConnected) {
                $this->rcon->disconnect();
                $this->isConnected = false;
            }
            Log::channel('single')->info('RCON connection closed');
            return response()->json(['message' => 'RCON connection closed']);
        }
    }
}