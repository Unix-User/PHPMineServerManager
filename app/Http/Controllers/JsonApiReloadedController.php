<?php

namespace App\Http\Controllers;

use App\Services\JSONAPI;
use Illuminate\Http\Request;

class JsonApiReloadedController extends Controller
{
    protected $jsonApiService;

    public function __construct(JSONAPI $jsonApiService)
    {
        $this->jsonApiService = $jsonApiService;
    }

    public function getLatestChatsWithLimit(Request $request)
    {
        return $this->jsonApiResponse('getLatestChatsWithLimit', ['limit' => $request->input('limit', 10)]);
    }

    public function runCommand(Request $request)
    {
        return $this->jsonApiResponse('runConsoleCommand', ['command' => $request->input('command')]);
    }

    public function getJavaMemoryUsage()
    {
        return $this->handleApiResponse($this->jsonApiService->getJavaMemoryUsage());
    }
    private function handleApiResponse($apiResponse)
    {
        if (isset($apiResponse['error'])) {
            return [
                'result' => 'error',
                'source' => $apiResponse['source'],
                'is_success' => false,
                'error' => [
                    'code' => $apiResponse['error']['code'],
                    'message' => $apiResponse['error']['message']
                ]
            ];
        } else {
            return [
                'result' => 'success',
                'success' => $apiResponse,
                'source' => $apiResponse,
                'is_success' => true
            ];
        }
    }

    public function teleportPlayer(Request $request)
    {
        return $this->jsonApiResponse('teleportPlayerToLocation', [
            'playerName' => $request->input('playerName'), 
            'x' => $request->input('x'), 
            'y' => $request->input('y'), 
            'z' => $request->input('z')
        ]);
    }

    public function givePlayerItem(Request $request)
    {
        return $this->jsonApiResponse('takePlayerInventory', [
            'playerName' => $request->input('playerName'), 
            'itemName' => $request->input('itemName'), 
            'quantity' => $request->input('quantity')
        ]);
    }

    public function setWorldTime(Request $request)
    {
        return $this->jsonApiResponse('setWorldTime', [
            'worldName' => $request->input('worldName'), 
            'time' => $request->input('time')
        ]);
    }

    public function getServerStats()
    {
        return $this->jsonApiResponse('getServerStats');
    }

    public function getServerVersion()
    {
        return $this->jsonApiResponse('getServerVersion');
    }

    public function banPlayer(Request $request)
    {
        $playerName = $request->input('playerName');
        return $this->jsonApiResponse('banPlayer', ['playerName' => $playerName]);
    }

    public function unbanPlayer(Request $request)
    {
        $playerName = $request->input('playerName');
        return $this->jsonApiResponse('unbanPlayer', ['playerName' => $playerName]);
    }

    public function getOnlinePlayersDetails()
    {
        return $this->jsonApiResponse('getOnlinePlayersDetails');
    }

    public function getPlayerCount()
    {
        return $this->jsonApiResponse('getPlayerCount');
    }

    public function getOnlinePlayerNamesInWorld(Request $request)
    {
        $worldName = $request->input('worldName');
        return $this->jsonApiResponse('getOnlinePlayerNamesInWorld', ['worldName' => $worldName]);
    }

    public function checkServerConnection()
    {
        $connectionStatus = $this->jsonApiService->checkConnection();
        return response()->json(['is_connected' => $connectionStatus], $connectionStatus ? 200 : 503);
    }

    private function jsonApiResponse($method, $parameters = [], $checkEmpty = false)
    {
        if ($checkEmpty && in_array(null, $parameters, true)) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        try {
            $response = $this->jsonApiService->call($method, $parameters);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'result' => 'error',
                'source' => $method,
                'is_success' => false,
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }
}
