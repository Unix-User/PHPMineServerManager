<?php

namespace App\Services;

use App\Services\OllamaService;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Serviço para gerenciamento de mensagens do Discord.
 * Permite enviar mensagens para canais do Discord, opcionalmente utilizando OllamaService para gerar conteúdo.
 */
class DiscordMessageService
{
    private ?OllamaService $ollamaService;
    private string $webhookUrl;
    private string $botToken;
    private string $channelId;
    private DiscordBotService $discordBotService;

    public function __construct(?OllamaService $ollamaService = null, DiscordBotService $discordBotService)
    {
        Log::info('Inicializando DiscordMessageService', [
            'ollamaService_configured' => !is_null($ollamaService),
            'webhookUrl_configured' => !empty(env('DISCORD_WEBHOOK_URL')),
            'botToken_configured' => !empty(env('DISCORD_TOKEN')),
            'channelId_configured' => !empty(env('DISCORD_CHANNEL_ID'))
        ]);

        $this->ollamaService = $ollamaService;
        $this->webhookUrl = env('DISCORD_WEBHOOK_URL');
        $this->botToken = env('DISCORD_TOKEN');
        $this->channelId = env('DISCORD_CHANNEL_ID');
        $this->discordBotService = $discordBotService;
    }

    public function sendChatMessage(string $message, ?string $channelId = null): void
    {
        $targetChannel = $channelId ?? $this->channelId;

        Log::info('Enviando mensagem para o Discord', [
            'channelId' => $targetChannel,
            'message_length' => strlen($message)
        ]);

        $response = $this->discordBotService->discordApiRequest('POST', "/channels/{$targetChannel}/messages", ['content' => $message]);

        if (is_null($response) || isset($response['code'])) {
            Log::error('Falha ao enviar mensagem para o Discord', [
                'channelId' => $targetChannel,
                'response' => $response
            ]);
            throw new Exception("Falha ao enviar mensagem para o Discord.");
        }

        Log::info('Mensagem enviada com sucesso para o Discord', [
            'channelId' => $targetChannel,
            'response_status' => $response['status'] ?? 'N/A'
        ]);
    }

    public function sendChatMessageWithOllama(string $prompt, array $ollamaOptions = [], ?string $channelId = null): void
    {
        if (!$this->ollamaService) {
            Log::error('OllamaService não está configurado para DiscordMessageService.');
            throw new Exception('OllamaService não está configurado para DiscordMessageService.');
        }

        try {
            Log::info('Gerando mensagem com Ollama', [
                'prompt_length' => strlen($prompt),
                'options' => $ollamaOptions
            ]);

            $response = $this->ollamaService->generate([
                'model' => env('OLLAMA_MODEL', 'google_genai.gemini-2.0-flash-exp'),
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt
                ]],
                'options' => $ollamaOptions
            ]);

            $messageContent = trim($response['choices'][0]['message']['content'] ?? '');
            
            if (!empty($messageContent)) {
                Log::info('Mensagem gerada pelo Ollama', [
                    'content_length' => strlen($messageContent)
                ]);
                $this->sendChatMessage($messageContent, $channelId);
            } else {
                Log::warning('Mensagem gerada pelo Ollama está vazia.');
            }
        } catch (Exception $e) {
            Log::error('Erro ao gerar e enviar mensagem com Ollama para o Discord', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getChannelMessages(?string $channelId = null, int $limit = 50): array
    {
        $targetChannel = $channelId ?? $this->channelId;

        Log::info('Obtendo mensagens do canal do Discord', [
            'channelId' => $targetChannel,
            'limit' => $limit
        ]);

        $messages = $this->discordBotService->discordApiRequest('GET', "/channels/{$targetChannel}/messages", ['limit' => $limit]);

        Log::info('Mensagens obtidas do canal do Discord', [
            'channelId' => $targetChannel,
            'message_count' => count($messages)
        ]);

        return $messages;
    }

    public function editMessage(string $messageId, string $newContent, ?string $channelId = null): void
    {
        $targetChannel = $channelId ?? $this->channelId;

        Log::info('Editando mensagem no Discord', [
            'channelId' => $targetChannel,
            'messageId' => $messageId,
            'newContent_length' => strlen($newContent)
        ]);

        $response = $this->discordBotService->discordApiRequest('PATCH', "/channels/{$targetChannel}/messages/{$messageId}", ['content' => $newContent]);

        if (is_null($response) || isset($response['code'])) {
            Log::error('Falha ao editar mensagem no Discord', [
                'channelId' => $targetChannel,
                'messageId' => $messageId,
                'response' => $response
            ]);
            throw new Exception("Falha ao editar mensagem no Discord.");
        }

        Log::info('Mensagem editada com sucesso no Discord', [
            'channelId' => $targetChannel,
            'messageId' => $messageId,
            'response_status' => $response['status'] ?? 'N/A'
        ]);
    }
}