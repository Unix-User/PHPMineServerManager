<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DiscordBotService
{
    protected $discordMessageService;
    protected $ollamaService;
    protected string $mrRobotId;

    public function __construct(DiscordMessageService $discordMessageService, OllamaService $ollamaService)
    {
        $this->discordMessageService = $discordMessageService;
        $this->ollamaService = $ollamaService;
        $this->mrRobotId = env('MR_ROBOT_ID', '1140237518978166896');
    }

    /**
     * Método genérico para fazer requisições à API do Discord.
     *
     * @param string $method Método HTTP (GET, POST, PATCH, DELETE, PUT).
     * @param string $endpoint Endpoint da API (ex: /channels/{channelId}/messages).
     * @param array $data Dados a serem enviados no corpo da requisição (para POST, PATCH, PUT).
     * @return array|null Resposta da API em formato JSON ou null em caso de erro.
     */
    public function discordApiRequest(string $method, string $endpoint, array $data = []): ?array
    {
        $baseUrl = 'https://discord.com/api/v10';
        $url = $baseUrl . $endpoint;
        $headers = [
            'Authorization' => 'Bot ' . env('DISCORD_TOKEN'),
            'Content-Type' => 'application/json',
        ];

        try {
            $response = Http::withHeaders($headers)
                ->withOptions(['verify' => true]) // Certifica-se de verificar o certificado SSL
                ->$method($url, $data);
            $response->throw(); // Lança exceção para erros HTTP 4xx e 5xx

            return $response->json();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Erro na requisição à API do Discord', [
                'url' => $url,
                'method' => $method,
                'status' => $e->response->status(),
                'body' => $e->response->body(),
                'discord_error' => $e->response->json() ?? 'N/A'
            ]);
            return $e->response->json() ?? ['message' => 'Erro ao processar a solicitação', 'code' => $e->response->status()];
        } catch (\Exception $e) {
            Log::error('Exceção ao fazer requisição à API do Discord', [
                'url' => $url,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            return ['message' => 'Erro ao processar a solicitação', 'code' => 500];
        }
    }

    public function handleDirectMessage($message)
    {
        try {
            $this->discordMessageService->sendChatMessage('Recebi sua mensagem: ' . $message['content'], $message['channel_id']);
        } catch (\Exception $e) {
            Log::error('Erro ao processar mensagem do Discord: ' . $e->getMessage());
        }
    }

    public function getChannelMessages($channelId)
    {
        try {
            return $this->discordApiRequest('GET', "/channels/{$channelId}/messages");
        } catch (\Exception $e) {
            Log::error('Erro ao obter mensagens do canal do Discord: ' . $e->getMessage());
            return [];
        }
    }

    public function processMessages($channelId)
    {
        $messages = $this->getChannelMessages($channelId);
        $processedMessages = Cache::get('processed_discord_messages_' . $channelId, []);

        foreach ($messages as $message) {
            if (!in_array($message['id'], $processedMessages)) {
                $processedMessages[] = $message['id'];
            }
        }

        Cache::put('processed_discord_messages_' . $channelId, $processedMessages, now()->addDay());
    }

    public function handleBotInteraction(array $message): void
    {
        Log::info('Interação com o bot', [
            'message_id' => $message['id'],
            'author' => $message['author']['username'],
            'content' => $message['content']
        ]);

        try {
            $prompt = "Você é Mr. Robot, um assistente de IA amigável. Responda à seguinte mensagem: {$message['content']}";
            $response = $this->ollamaService->generate([
                'model' => env('OLLAMA_MODEL'),
                'prompt' => $prompt,
                'stream' => false,
                'system' => "You are Mr. Robot, a friendly AI assistant."
            ]);

            if (isset($response['response'])) {
                $this->discordMessageService->sendChatMessage($response['response'], $message['channel_id']);
                Log::info('Resposta enviada', [
                    'message_id' => $message['id'],
                    'response' => $response['response']
                ]);
            } else {
                throw new \Exception("Resposta do Ollama não contém a chave 'response'");
            }
        } catch (\Exception $e) {
            Log::error('Erro ao gerar resposta', [
                'message_id' => $message['id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'message_content' => $message['content']
            ]);
            $this->discordMessageService->sendChatMessage('Desculpe, ocorreu um erro ao processar sua solicitação.', $message['channel_id']);
            $this->notifyAdminOfError($e, ['message_content' => $message['content'], 'message_id' => $message['id']]); // Adiciona contexto ao notifyAdminOfError
        }
    }

    private function notifyAdminOfError(\Exception $e, array $context = [])
    {
        $context['error'] = $e->getMessage();
        $context['trace'] = $e->getTraceAsString();
        Log::critical('Erro crítico detectado, administrador precisa ser notificado', $context);
        // TODO: Implementar notificação para o administrador (e-mail, Slack, etc.)
    }

    /**
     * Cria um canal de mensagem direta com um usuário.
     *
     * @param string $recipientId ID do usuário destinatário.
     * @return array|null Dados do canal de mensagem direta ou null em caso de erro.
     */
    public function createDirectMessageChannel(string $recipientId): ?array
    {
        $url = "/users/@me/channels";
        $data = [
            'recipient_id' => $recipientId,
        ];

        return $this->discordApiRequest('POST', $url, $data);
    }
}
