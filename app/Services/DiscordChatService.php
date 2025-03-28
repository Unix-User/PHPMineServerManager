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
        Log::info('DiscordChatService inicializado', [
            'service' => 'DiscordChatService',
            'initialized' => true
        ]);
    }

    public function fetchChatMessages(): array
    {
        Log::info('Iniciando busca de mensagens do Discord');
        
        try {
            $channelId = config('services.discord.channel_id');
            Log::debug('ID do canal obtido da configuração', [
                'channel_id' => $channelId
            ]);

            if (!$channelId) {
                $errorMsg = 'ID do canal do Discord não configurado.';
                Log::error($errorMsg);
                throw new Exception($errorMsg);
            }

            Log::info('Buscando mensagens do canal do Discord', [
                'channel_id' => $channelId
            ]);
            
            $messages = $this->discordBotService->getChannelMessages($channelId);
            Log::info('Mensagens recebidas do Discord', [
                'message_count' => count($messages)
            ]);

            $processedMessages = array_map(function ($message) {
                $processed = [
                    'author_username' => $message['author']['username'] ?? 'Desconhecido',
                    'content' => $message['content'] ?? '',
                    'timestamp' => $message['timestamp'] ?? now()->toDateTimeString(),
                ];
                
                Log::debug('Mensagem processada', $processed);
                return $processed;
            }, $messages);

            Log::info('Processamento de mensagens concluído', [
                'processed_messages_count' => count($processedMessages)
            ]);

            return $processedMessages;

        } catch (Exception $e) {
            Log::error('Erro ao buscar mensagens do Discord', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
}