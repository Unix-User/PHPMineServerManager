<?php

namespace App\Services;

use App\Http\Controllers\JsonApiReloadedController;
use Illuminate\Http\Request;
use Exception;

class MinecraftChatService
{
    private const MESSAGE_TIME_LIMIT = 30000;

    public function fetchChatMessages(): array
    {
        $response = app(JsonApiReloadedController::class)
            ->getLatestChatsWithLimit(new Request(['limit' => 100]));

        if (!isset($response->original[0]['success'])) {
            throw new Exception('Resposta inválida do servidor Minecraft: Formato inesperado');
        }

        $messages = $response->original[0]['success'];
        $currentTime = time();

        return array_filter($messages, function($message) use ($currentTime) {
            return ($currentTime - $message['time']) <= self::MESSAGE_TIME_LIMIT;
        });
    }

    public function sendChatMessage(string $player, string $message): void
    {
        $response = app(JsonApiReloadedController::class)->sendMessage(
            new Request(['playerName' => $player, 'message' => $message])
        );

        if (!isset($response->original['success'])) {
            throw new Exception("Falha ao enviar mensagem para {$player}");
        }
    }
}