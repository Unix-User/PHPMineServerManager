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
    private ?OllamaService $ollamaService; // OllamaService agora é opcional
    private string $webhookUrl;

    public function __construct(?OllamaService $ollamaService = null)
    {
        $this->ollamaService = $ollamaService; // Injeção de dependência opcional
        $this->webhookUrl = env('DISCORD_WEBHOOK_URL'); // Assume que a URL do webhook está configurada no .env
    }

    /**
     * Envia uma mensagem para um canal do Discord usando um webhook.
     *
     * @param string $message Conteúdo da mensagem a ser enviada.
     * @return void
     * @throws Exception Se a URL do webhook não estiver configurada ou se houver um erro ao enviar a mensagem.
     */
    public function sendChatMessage(string $message): void
    {
        if (empty($this->webhookUrl)) {
            Log::error('Webhook URL do Discord não configurada.');
            throw new Exception('Webhook URL do Discord não configurada.');
        }

        try {
            $response = Http::post($this->webhookUrl, [
                'content' => $message,
            ]);

            if (!$response->successful()) {
                Log::error('Falha ao enviar mensagem para o Discord', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception("Falha ao enviar mensagem para o Discord. Status: {$response->status()}");
            }
        } catch (Exception $e) {
            Log::error('Erro ao enviar mensagem para o Discord', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Gera uma mensagem utilizando o OllamaService e a envia para o Discord.
     *
     * @param string $prompt Prompt a ser enviado para o OllamaService.
     * @param array $ollamaOptions Opções adicionais para a requisição ao Ollama.
     * @return void
     * @throws Exception Se o OllamaService não estiver configurado ou se houver um erro na geração/envio da mensagem.
     */
    public function sendChatMessageWithOllama(string $prompt, array $ollamaOptions = []): void
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
                $this->sendChatMessage($messageContent);
            } else {
                Log::warning('Mensagem gerada pelo Ollama está vazia.');
            }
        } catch (Exception $e) {
            Log::error('Erro ao gerar e enviar mensagem com Ollama para o Discord', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}