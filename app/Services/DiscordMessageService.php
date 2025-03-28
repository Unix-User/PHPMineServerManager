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
    private DiscordBotService $discordBotService; // Adiciona dependência

    public function __construct(?OllamaService $ollamaService = null, DiscordBotService $discordBotService) // Injeta DiscordBotService
    {
        $this->ollamaService = $ollamaService;
        $this->webhookUrl = env('DISCORD_WEBHOOK_URL');
        $this->botToken = env('DISCORD_TOKEN');
        $this->channelId = env('DISCORD_CHANNEL_ID');
        $this->discordBotService = $discordBotService; // Inicializa
    }

    /**
     * Envia uma mensagem para um canal do Discord usando a API.
     *
     * @param string $message Conteúdo da mensagem a ser enviada.
     * @param string|null $channelId ID do canal (opcional, usa o padrão se não fornecido)
     * @return void
     * @throws Exception Se a URL do webhook não estiver configurada ou se houver um erro ao enviar a mensagem.
     */
    public function sendChatMessage(string $message, ?string $channelId = null): void
    {
        $targetChannel = $channelId ?? $this->channelId;

        $response = $this->discordBotService->discordApiRequest('POST', "/channels/{$targetChannel}/messages", ['content' => $message]);

        if (is_null($response) || isset($response['code'])) {
            Log::error('Falha ao enviar mensagem para o Discord', [
                'channelId' => $targetChannel,
                'response' => $response
            ]);
            throw new Exception("Falha ao enviar mensagem para o Discord.");
        }
    }

    /**
     * Gera uma mensagem utilizando o OllamaService e a envia para o Discord.
     *
     * @param string $prompt Prompt a ser enviado para o OllamaService.
     * @param array $ollamaOptions Opções adicionais para a requisição ao Ollama.
     * @param string|null $channelId ID do canal (opcional, usa o padrão se não fornecido)
     * @return void
     * @throws Exception Se o OllamaService não estiver configurado ou se houver um erro na geração/envio da mensagem.
     */
    public function sendChatMessageWithOllama(string $prompt, array $ollamaOptions = [], ?string $channelId = null): void
    {
        if (!$this->ollamaService) {
            Log::error('OllamaService não está configurado para DiscordMessageService.');
            throw new Exception('OllamaService não está configurado para DiscordMessageService.');
        }

        try {
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
                $this->sendChatMessage($messageContent, $channelId);
            } else {
                Log::warning('Mensagem gerada pelo Ollama está vazia.');
            }
        } catch (Exception $e) {
            Log::error('Erro ao gerar e enviar mensagem com Ollama para o Discord', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Obtém as últimas mensagens de um canal do Discord usando a API.
     *
     * @param string|null $channelId ID do canal (opcional, usa o padrão se não fornecido)
     * @param int $limit Número máximo de mensagens a serem retornadas (padrão: 50)
     * @return array
     * @throws Exception Se houver erro na requisição
     */
    public function getChannelMessages(?string $channelId = null, int $limit = 50): array
    {
        $targetChannel = $channelId ?? $this->channelId;

        return $this->discordBotService->discordApiRequest('GET', "/channels/{$targetChannel}/messages", ['limit' => $limit]);
    }

    /**
     * Edita uma mensagem existente no Discord usando a API.
     *
     * @param string $messageId ID da mensagem a ser editada
     * @param string $newContent Novo conteúdo da mensagem
     * @param string|null $channelId ID do canal (opcional, usa o padrão se não fornecido)
     * @return void
     * @throws Exception Se houver erro na requisição
     */
    public function editMessage(string $messageId, string $newContent, ?string $channelId = null): void
    {
        $targetChannel = $channelId ?? $this->channelId;

        $response = $this->discordBotService->discordApiRequest('PATCH', "/channels/{$targetChannel}/messages/{$messageId}", ['content' => $newContent]);

        if (is_null($response) || isset($response['code'])) {
            Log::error('Falha ao editar mensagem no Discord', [
                'channelId' => $targetChannel,
                'messageId' => $messageId,
                'response' => $response
            ]);
            throw new Exception("Falha ao editar mensagem no Discord.");
        }
    }
}