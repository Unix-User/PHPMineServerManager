<?php

namespace App\Services;

use App\Services\DiscordBotService;
use Exception;
use Illuminate\Support\Facades\Log;

class DiscordChatService
{
    private DiscordBotService $discordBotService;

    public function __construct(DiscordBotService $discordBotService)
    {
        $this->discordBotService = $discordBotService;
    }

    public function fetchChatMessages(): array
    {
        try {
            $channelId = config('services.discord.channel_id');
            if (!$channelId) {
                throw new Exception('ID do canal do Discord não configurado.');
            }

            $messages = $this->discordBotService->getChannelMessages($channelId);

            return array_map(function ($message) {
                return [
                    'author_username' => $message['author']['username'] ?? 'Desconhecido',
                    'content' => $message['content'] ?? '',
                    'timestamp' => $message['timestamp'] ?? now()->toDateTimeString(),
                ];
            }, $messages);

        } catch (Exception $e) {
            Log::error('Erro ao buscar mensagens do Discord: ' . $e->getMessage());
            return [];
        }
    }
}