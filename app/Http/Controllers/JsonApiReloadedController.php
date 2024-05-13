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

    public function verifyApiStatus()
    {
        $response = $this->jsonApiService->isConnected() ? 
                    ['status' => 'success', 'message' => 'API connection is active.'] : 
                    ['status' => 'failure', 'message' => 'Unable to connect to API.'];
        return response()->json($response, $response['status'] === 'success' ? 200 : 500);
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
