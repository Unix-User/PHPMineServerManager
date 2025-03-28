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
    protected $ollamaService;

    public function __construct(DiscordBotService $discordService, OllamaService $ollamaService)
    {
        $this->discordService = $discordService;
        $this->ollamaService = $ollamaService;
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

    public function sendMessage(Request $request, $content)
    {
        $webhookUrl = env('DISCORD_WEBHOOK_URL');
        $user = auth()->user();
        $response = Http::withHeaders($this->headers)
            ->post($webhookUrl, [
            'content' => $content,
            'username' => $user->name,
            'avatar_url' => $user->profile_photo_url
        ]);

        if ($response->successful()) {
            $this->handleBotResponse($content);
        }

        return $response;
    }

    private function handleBotResponse($content)
    {
        try {
            $prompt = "Você é Mr. Robot, um assistente de IA amigável. Responda à seguinte mensagem: {$content}";
            $ollama_response = $this->ollamaService->generate([
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

            if (isset($ollama_response['choices'][0]['message']['content'])) {
                $botResponse = $ollama_response['choices'][0]['message']['content'];
                $this->sendBotMessage($botResponse);
                Log::info('Resposta do bot enviada', ['response' => $botResponse]);
            } else {
                throw new \Exception("Resposta do Ollama não contém o conteúdo esperado");
            }
        } catch (\Exception $e) {
            Log::error('Erro ao gerar resposta do bot', ['error' => $e->getMessage(), 'content' => $content]);
            $this->notifyAdminOfError($e, ['content' => $content]);
        }
    }

    private function sendBotMessage($content, $channelId = null)
    {
        $channel = $channelId ?? $this->channelId;
        return $this->discordService->discordApiRequest('POST', "/channels/{$channel}/messages", ['content' => $content]);
    }

    public function getChannelMessages()
    {
        if (RateLimiter::tooManyAttempts('check_discord_messages', 60)) {
            Log::warning('Rate limit exceeded for Discord API calls');
            return;
        }

        RateLimiter::hit('check_discord_messages');

        $this->processChannelMessages($this->channelId);
        $this->processDirectMessages();
    }

    private function processDirectMessages()
    {
        if (RateLimiter::tooManyAttempts('check_discord_dm', 60)) {
            Log::warning('Rate limit exceeded for Discord API calls (DMs)');
            return;
        }

        RateLimiter::hit('check_discord_dm');

        $dmChannel = $this->discordService->createDirectMessageChannel($this->mrRobotId);

        if ($dmChannel && isset($dmChannel['type']) && $dmChannel['type'] === 1) {
            $this->processMessages($dmChannel['id'], 'processed_discord_dm_messages');
        } else {
            Log::error('Falha ao criar canal de mensagem direta ou resposta inválida do Discord', [
                'mrRobotId' => $this->mrRobotId,
                'response' => $dmChannel
            ]);
        }
    }

    private function processMessages($channelId, $cacheKey)
    {
        $response = $this->discordService->discordApiRequest('GET', "/channels/{$channelId}/messages");

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
                    $this->handleBotInteraction($message);
                }
                $newProcessedMessages[] = $message['id'];
            }
        }

        $newProcessedMessages = array_slice($newProcessedMessages, -100);
        if ($newProcessedMessages !== $processedMessages) {
            Cache::put($cacheKey . '_' . $channelId, $newProcessedMessages, now()->addDay());
        }
    }

    private function processChannelMessages($channelId)
    {
        $this->processMessages($channelId, 'processed_discord_messages');
    }

    private function handleBotInteraction($message)
    {
        $this->discordService->handleBotInteraction($message);
    }

    public function getServerUpdates()
    {
        return $this->discordService->discordApiRequest('GET', "/channels/{$this->updateChannelId}/messages");
    }

    public function createRole(Request $request)
    {
        return $this->discordService->discordApiRequest('POST', "/guilds/{$this->serverId}/roles", ['name' => $request->input('name')]);
    }

    public function getRoles()
    {
        $cacheKey = 'discord_server_roles_' . $this->serverId;
        $roles = Cache::remember($cacheKey, now()->addMinutes(10), function () { // Cache por 10 minutos
            return $this->discordService->discordApiRequest('GET', "/guilds/{$this->serverId}/roles");
        });
        return $roles;
    }

    public function updateRole(Request $request, $roleId)
    {
        return $this->discordService->discordApiRequest('PATCH', "/guilds/{$this->serverId}/roles/{$roleId}", ['name' => $request->input('name')]);
    }

    public function deleteRole($roleId)
    {
        return $this->discordService->discordApiRequest('DELETE', "/guilds/{$this->serverId}/roles/{$roleId}");
    }

    public function assignRole($userId, $roleId)
    {
        return $this->discordService->discordApiRequest('PUT', "/guilds/{$this->serverId}/members/{$userId}/roles/{$roleId}");
    }

    public function removeRole($userId, $roleId)
    {
        return $this->discordService->discordApiRequest('DELETE', "/guilds/{$this->serverId}/members/{$userId}/roles/{$roleId}");
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


    private function notifyAdminOfError(\Exception $e, array $context = [])
    {
        $context['error'] = $e->getMessage();
        $context['trace'] = $e->getTraceAsString();
        // TODO: Implementar a lógica para notificar os administradores sobre erros críticos
        // Pode ser via e-mail, Slack, ou outro método de sua escolha
        Log::critical('Erro crítico detectado, administrador precisa ser notificado', $context);
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
            case 1:
                return response()->json(['type' => 1]);

            case 2:
                return $this->handleApplicationCommand($payload);

            case 3:
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