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
        $response = Http::withHeaders($this->headers)->post($webhookUrl, [
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
            $ollama_response = $ollamaService->generate(
                env('OLLAMA_MODEL', 'llama3.2'),
                $content
            );
            $this->sendBotMessage($ollama_response['response']);
            Log::info('Resposta do bot enviada', ['response' => $ollama_response['response']]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar resposta do bot', ['error' => $e->getMessage()]);
            $this->notifyAdminOfError($e);
        }
    }

    private function sendBotMessage($content, $channelId = null)
    {
        $channel = $channelId ?? $this->channelId;
        return Http::retry(3, 100)->withHeaders($this->headers)->post("https://discord.com/api/v10/channels/{$channel}/messages", [
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

        $this->processMessages($this->channelId, $ollamaService);
        $this->getDirectMessages($ollamaService);
    }

    private function getDirectMessages(OllamaService $ollamaService)
    {
        if (RateLimiter::tooManyAttempts('check_discord_dm', 60)) {
            Log::warning('Rate limit exceeded for Discord API calls');
            return;
        }

        RateLimiter::hit('check_discord_dm');

        $dmChannel = $this->discordService->createDirectMessageChannel($this->mrRobotId);

        if (isset($dmChannel['type']) && $dmChannel['type'] === 1) {
            $messages = $this->discordService->getChannelMessages($dmChannel['id']);

            if (isset($messages['message'])) {
                Log::error('Erro ao obter mensagens do canal de DM: ' . $messages['message']);
                return; // Exit if there's an error
            }

            foreach ($messages as $message) {
                if ($message['author']['id'] === $this->mrRobotId) {
                    continue;
                }

                $this->discordService->sendMessage($dmChannel['id'], "Olá, você disse: {$message['content']}");
            }
        }
    }

    private function processMessages($channelId, OllamaService $ollamaService)
    {
        $response = $this->sendRequest('GET', "/channels/{$channelId}/messages");

        if (is_array($response) && isset($response['message'])) {
            Log::error('Erro ao obter mensagens do canal', ['channelId' => $channelId, 'error' => $response['message'], 'code' => $response['code'] ?? 'N/A']);
            return;
        }

        if (!is_array($response)) {
            Log::error('Resposta inesperada da API do Discord ao obter mensagens do canal', ['channelId' => $channelId, 'response' => $response]);
            return;
        }

        $processedMessages = Cache::get('processed_discord_messages_' . $channelId, []);

        foreach ($response as $message) {
            if (!in_array($message['id'], $processedMessages) && $message['author']['id'] !== $this->mrRobotId) {
                if (strpos($message['content'], '<@' . $this->mrRobotId . '>') !== false) {
                    $this->handleMentionResponse($message, $ollamaService);
                } else {
                    $this->handleDirectMessageResponse($message, $ollamaService);
                }
                $processedMessages[] = $message['id'];
            }
        }

        $processedMessages = array_slice($processedMessages, -100);
        Cache::put('processed_discord_messages_' . $channelId, $processedMessages, now()->addDay());
    }

    private function handleMentionResponse($message, OllamaService $ollamaService)
    {
        Log::info('Mr. Robot mencionado', [
            'message_id' => $message['id'],
            'author' => $message['author']['username'],
            'content' => $message['content']
        ]);

        try {
            $response = $ollamaService->generate([
                'model' => env('OLLAMA_MODEL', 'llama3.2'),
                'prompt' => "Você é Mr. Robot, um assistente de IA amigável. Responda à seguinte mensagem: {$message['content']}",
                'stream' => false,
                'system' => "You are Mr. Robot, a friendly AI assistant."
            ]);

            if (isset($response['response'])) {
                $this->sendBotMessage($response['response'], $this->channelId);
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
                'trace' => $e->getTraceAsString()
            ]);
            $this->sendBotMessage('Desculpe, ocorreu um erro ao processar sua solicitação.', $this->channelId);
            $this->notifyAdminOfError($e);
        }
    }

    private function handleDirectMessageResponse($message, OllamaService $ollamaService)
    {
        Log::info('Mensagem direta recebida', [
            'message_id' => $message['id'],
            'author' => $message['author']['username'],
            'content' => $message['content']
        ]);

        try {
            $response = $ollamaService->generate([
                'model' => env('OLLAMA_MODEL', 'llama3.2'),
                'prompt' => "Você é Mr. Robot, um assistente de IA amigável. Responda à seguinte mensagem: {$message['content']}",
                'stream' => false,
                'system' => "You are Mr. Robot, a friendly AI assistant."
            ]);

            if (isset($response['response'])) {
                $this->sendBotMessage($response['response'], $this->channelId);
                Log::info('Resposta enviada ao canal do bot', [
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
                'trace' => $e->getTraceAsString()
            ]);
            $this->sendBotMessage('Desculpe, ocorreu um erro ao processar sua solicitação.', $this->channelId);
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
            $response = Http::withHeaders($this->headers)->$method($url, $data);

            if ($response->failed()) {
                $body = null;
                try {
                    $body = $response->json();
                } catch (\Exception $e) {
                    $body = $response->body();
                }

                Log::error('Discord API request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $body
                ]);
                return ['message' => 'Erro ao processar a solicitação', 'code' => $response->status()];
            }

            return $response->json();
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
        // Implemente aqui a lógica para notificar os administradores sobre erros críticos
        // Pode ser via e-mail, Slack, ou outro método de sua escolha
    }

    public function handleWebhook(Request $request)
    {
        $signature = $request->header('X-Discord-Signature');
        $this->verifyWebhookSignature($signature, $request->getContent());

        $payload = $request->all();

        if (!isset($payload['type'])) {
            Log::error('Payload does not contain type', ['payload' => $payload]);
            return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
        }

        switch ($payload['type']) {
            case 1:
                return response()->json(['type' => 1]);

            case 2:
                return $this->handleApplicationCommand($payload);

            case 3:
                return $this->handleMessageComponent($payload);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function verifyWebhookSignature($signature, $payload)
    {
        // Implementar verificação de assinatura
    }

    protected function handleApplicationCommand($payload)
    {
        // Lógica para lidar com comandos de aplicação
    }

    protected function handleMessageComponent($payload)
    {
        // Lógica para lidar com componentes de mensagem
    }
}