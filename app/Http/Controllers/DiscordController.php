<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use App\Services\OllamaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\DiscordBotService;
use Illuminate\Support\Arr;

class DiscordController extends Controller
{
    private $discordToken;
    private $channelId;
    private $updateChannelId;
    private $serverId;
    private $headers;
    private $mrRobotId;
    protected $discordService;

    public function __construct(DiscordBotService $discordService)
    {
        $this->discordService = $discordService;
        $this->discordToken = env('DISCORD_TOKEN');
        $this->channelId = env('DISCORD_CHANNEL_ID');
        $this->updateChannelId = env('DISCORD_UPDATE_CHANNEL_ID');
        $this->serverId = env('DISCORD_SERVER_ID');
        $this->headers = [
            'Authorization' => 'Bot ' . $this->discordToken,
            'Content-Type' => 'application/json',
        ];
        $this->mrRobotId = env('MR_ROBOT_ID', '1140237518978166896');
    }

    public function sendMessage(Request $request, $content, OllamaService $ollamaService)
    {
        $webhookUrl = env('DISCORD_WEBHOOK_URL');
        $user = auth()->user();
        $response = Http::withHeaders($this->headers)
            ->verify(env('DISCORD_VERIFY_SSL', true)) // Alterado de false para true
            ->post($webhookUrl, [
            'content' => $content,
            'username' => $user->name,
            'avatar_url' => $user->profile_photo_url
        ]);

        if ($response->successful()) {
            $this->handleBotResponse($content, $ollamaService);
        }

        return $response;
    }

    private function handleBotResponse($content, OllamaService $ollamaService)
    {
        try {
            $prompt = "Você é Mr. Robot, um assistente de IA amigável. Responda à seguinte mensagem: {$content}";
            $ollama_response = $ollamaService->generate([
                'model' => env('OLLAMA_MODEL'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'stream' => false,
                'system' => "You are Mr. Robot, a friendly AI assistant."
            ]);

            $this->sendBotMessage($ollama_response['response']);
            Log::info('Resposta do bot enviada', ['response' => $ollama_response['response']]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar resposta do bot', ['error' => $e->getMessage(), 'content' => $content]);
            $this->notifyAdminOfError($e);
        }
    }

    private function sendBotMessage($content, $channelId = null)
    {
        $channel = $channelId ?? $this->channelId;
        return Http::retry(3, 100)
            ->withHeaders($this->headers)
            ->verify(env('DISCORD_VERIFY_SSL', true)) // Alterado de false para true
            ->post("https://discord.com/api/v10/channels/{$channel}/messages", [
            'content' => $content,
        ]);
    }

    public function getChannelMessages(OllamaService $ollamaService)
    {
        if (RateLimiter::tooManyAttempts('check_discord_messages', 60)) {
            Log::warning('Rate limit exceeded for Discord API calls');
            return;
        }

        RateLimiter::hit('check_discord_messages');

        $this->processChannelMessages($this->channelId, $ollamaService);
        $this->processDirectMessages($ollamaService);
    }

    private function processDirectMessages(OllamaService $ollamaService)
    {
        if (RateLimiter::tooManyAttempts('check_discord_dm', 60)) {
            Log::warning('Rate limit exceeded for Discord API calls (DMs)');
            return;
        }

        RateLimiter::hit('check_discord_dm');

        $dmChannel = $this->discordService->createDirectMessageChannel($this->mrRobotId);

        if (isset($dmChannel['type']) && $dmChannel['type'] === 1) {
            $this->processMessages($dmChannel['id'], $ollamaService, 'processed_discord_dm_messages');
        }
    }

    private function processMessages($channelId, OllamaService $ollamaService, $cacheKey)
    {
        $response = $this->sendRequest('GET', "/channels/{$channelId}/messages");

        if (is_array($response) && isset($response['message'])) {
            Log::error('Erro ao obter mensagens do canal', [
                'channelId' => $channelId,
                'error' => $response['message'],
                'code' => $response['code'] ?? 'N/A'
            ]);
            return;
        }

        if (!is_array($response)) {
            Log::error('Resposta inesperada da API do Discord ao obter mensagens do canal', [
                'channelId' => $channelId,
                'response' => $response
            ]);
            return;
        }


        $processedMessages = Cache::get($cacheKey . '_' . $channelId, []);
        $newProcessedMessages = $processedMessages;

        foreach ($response as $message) {
            if (!in_array($message['id'], $processedMessages) && $message['author']['id'] !== $this->mrRobotId) {
                if (strpos($message['content'], '<@' . $this->mrRobotId . '>') !== false) {
                    $this->handleBotInteraction($message, $ollamaService);
                }
                $newProcessedMessages[] = $message['id'];
            }
        }

        $newProcessedMessages = array_slice($newProcessedMessages, -100);
        if ($newProcessedMessages !== $processedMessages) {
            Cache::put($cacheKey . '_' . $channelId, $newProcessedMessages, now()->addDay());
        }
    }

    private function processChannelMessages($channelId, OllamaService $ollamaService)
    {
        $this->processMessages($channelId, $ollamaService, 'processed_discord_messages');
    }


    private function handleBotInteraction($message, OllamaService $ollamaService)
    {
        Log::info('Interação com o bot', [
            'message_id' => $message['id'],
            'author' => $message['author']['username'],
            'content' => $message['content']
        ]);

        try {
            $prompt = "Você é Mr. Robot, um assistente de IA amigável. Responda à seguinte mensagem: {$message['content']}";
            $response = $ollamaService->generate([
                'model' => env('OLLAMA_MODEL'),
                'prompt' => $prompt,
                'stream' => false,
                'system' => "You are Mr. Robot, a friendly AI assistant."
            ]);

            if (isset($response['response'])) {
                $this->sendBotMessage($response['response'], $message['channel_id']);
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
            $this->sendBotMessage('Desculpe, ocorreu um erro ao processar sua solicitação.', $message['channel_id']);
            $this->notifyAdminOfError($e);
        }
    }


    public function getServerUpdates()
    {
        return $this->sendRequest('GET', "/channels/{$this->updateChannelId}/messages");
    }

    public function createRole(Request $request)
    {
        return $this->sendRequest('POST', "/guilds/{$this->serverId}/roles", ['name' => $request->input('name')]);
    }

    public function getRoles()
    {
        return $this->sendRequest('GET', "/guilds/{$this->serverId}/roles");
    }

    public function updateRole(Request $request, $roleId)
    {
        return $this->sendRequest('PATCH', "/guilds/{$this->serverId}/roles/{$roleId}", ['name' => $request->input('name')]);
    }

    public function deleteRole($roleId)
    {
        return $this->sendRequest('DELETE', "/guilds/{$this->serverId}/roles/{$roleId}");
    }

    public function assignRole($userId, $roleId)
    {
        return $this->sendRequest('PUT', "/guilds/{$this->serverId}/members/{$userId}/roles/{$roleId}");
    }

    public function removeRole($userId, $roleId)
    {
        return $this->sendRequest('DELETE', "/guilds/{$this->serverId}/members/{$userId}/roles/{$roleId}");
    }

    public function redirectToDiscord()
    {
        return Socialite::driver('discord')->redirect();
    }

    public function handleDiscordCallback()
    {
        $user = Socialite::driver('discord')->user();
        // $user->token
    }

    private function sendRequest($method, $endpoint, $data = [])
    {
        $baseUrl = 'https://discord.com/api/v10';
        $url = $baseUrl . $endpoint;

        try {
            $response = Http::withHeaders($this->headers)
                ->withOptions(['verify' => env('DISCORD_VERIFY_SSL', true)]) // Alterado para withOptions para compatibilidade com versões mais antigas do Laravel
                ->$method($url, $data);
            $response->throw();

            return $response->json();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Discord API request failed', [
                'url' => $url,
                'status' => $e->response->status(),
                'body' => $e->response->body(),
                'discord_error' => $e->response->json()
            ]);
            // Retorna o erro específico do Discord se disponível
            return $e->response->json() ?? ['message' => 'Erro ao processar a solicitação', 'code' => $e->response->status()];
        } catch (\Exception $e) {
            Log::error('Exception in Discord API request', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return ['message' => 'Erro ao processar a solicitação', 'code' => 500];
        }
    }

    private function notifyAdminOfError(\Exception $e)
    {
        // TODO: Implementar a lógica para notificar os administradores sobre erros críticos
        // Pode ser via e-mail, Slack, ou outro método de sua escolha
        Log::critical('Erro crítico detectado, administrador precisa ser notificado', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }

    public function handleWebhook(Request $request)
    {
        $signature = $request->header('X-Discord-Signature');
        $payload = $request->getContent();

        if (!$this->verifyWebhookSignature($signature, $payload)) {
            Log::warning('Webhook request não pôde ser verificada. Assinatura inválida.');
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        }


        $payload = $request->all();

        if (!isset($payload['type'])) {
            Log::error('Payload do webhook não contém o tipo', ['payload' => $payload]);
            return response()->json(['status' => 'error', 'message' => 'Invalid payload type'], 400);
        }

        switch ($payload['type']) {
            case 1: // PING
                return response()->json(['type' => 1]); // PONG

            case 2: // APPLICATION_COMMAND
                return $this->handleApplicationCommand($payload);

            case 3: // MESSAGE_COMPONENT
                return $this->handleMessageComponent($payload);

            default:
                Log::warning('Tipo de payload de webhook desconhecido', ['type' => $payload['type']]);
                return response()->json(['status' => 'warning', 'message' => 'Unknown payload type'], 200);
        }
    }

    protected function verifyWebhookSignature($signature, $payload): bool
    {
        // TODO: Implementar a verificação da assinatura do webhook usando a chave pública do Discord.
        // Documentação do Discord: https://discord.com/developers/docs/interactions/receiving-and-responding#security-and-authorization
        Log::warning('VERIFICAÇÃO DE ASSINATURA DO WEBHOOK NÃO IMPLEMENTADA. ISSO É UM RISCO DE SEGURANÇA!');
        // Por enquanto, retorna true para permitir o desenvolvimento, mas ISSO DEVE SER IMPLEMENTADO CORRETAMENTE.
        return true;
    }

    protected function handleApplicationCommand($payload)
    {
        // TODO: Implementar lógica para lidar com comandos de aplicação (slash commands)
        Log::info('Comando de aplicação recebido', ['command_data' => Arr::get($payload, 'data.name', 'N/A')]);
        // Exemplo de resposta imediata para um comando (ACK)
        return response()->json([
            'type' => 4, // CHANNEL_MESSAGE_WITH_SOURCE
            'data' => [
                'content' => 'Comando recebido! Processando...',
            ]
        ]);
        // Para respostas deferidas ou mais complexas, consulte a documentação do Discord.
    }

    protected function handleMessageComponent($payload)
    {
        // TODO: Implementar lógica para lidar com componentes de mensagem (botões, menus)
        Log::info('Componente de mensagem recebido', ['component_type' => Arr::get($payload, 'data.component_type', 'N/A'), 'custom_id' => Arr::get($payload, 'data.custom_id', 'N/A')]);
        // Exemplo de resposta imediata para um componente (ACK)
        return response()->json([
            'type' => 4, // CHANNEL_MESSAGE_WITH_SOURCE
            'data' => [
                'content' => 'Interação com componente recebida!',
            ]
        ]);
        // Para respostas deferidas ou mais complexas, consulte a documentação do Discord.
    }
}