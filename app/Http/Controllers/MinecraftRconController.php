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
    private $rconHost;
    private $rconPort;
    private $rconPassword;

    public function __construct()
    {
        $this->rconHost = env('RCON_SERVER', '127.0.0.1');
        $this->rconPort = env('RCON_PORT', 25575);
        $this->rconPassword = env('RCON_PASSWORD', null);
        $this->rcon = new Rcon($this->rconHost, $this->rconPort, $this->rconPassword, 3);
    }

    public function executeInternalCommand(Request $request)
    {
        $validatedData = $this->validateRequest($request);
        return $this->sendCommandToServer($validatedData['command']);
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
                try {
                    $this->isConnected = $this->rcon->connect();
                } catch (\Exception $e) {
                    Log::channel('single')->error('RCON connection failed', [
                        'host' => $this->rconHost,
                        'port' => $this->rconPort,
                        'error' => $e->getMessage(),
                    ]);
                    return response()->json(['response' => 'Não foi possível conectar ao servidor Minecraft. Verifique o log para mais detalhes.'], 500);
                }
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
            try {
                $this->isConnected = $this->rcon->connect();
            } catch (\Exception $e) {
                Log::channel('single')->error('RCON connection failed', [
                    'host' => $this->rconHost,
                    'port' => $this->rconPort,
                    'error' => $e->getMessage(),
                ]);
                return response()->json(['response' => 'Não foi possível conectar ao servidor Minecraft. Verifique o log para mais detalhes.'], 500);
            }
        }
        if (!$this->isConnected) {
            return response()->json(['response' => 'Não foi possível conectar ao servidor Minecraft.'], 500);
        }
        try {
            $response = $this->rcon->sendCommand($command);
        } catch (\Exception $e) {
            Log::channel('single')->error('Erro ao executar o comando RCON', [
                'command' => $command,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['response' => 'Falha ao executar o comando.'], 500);
        }
        if ($response === false) {
            return response()->json(['response' => 'Falha ao executar o comando.'], 500);
        }
        // Clean up special characters from the Minecraft RCON server response
        $cleanResponse = preg_replace('/§./', '', $response);
        Log::channel('single')->info('Command response: ' . $cleanResponse);
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
            return response()->json(['message' => 'Conexão RCON fechada.']);
        }
    }
}