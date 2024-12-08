<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use App\Services\OllamaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class DiscordController extends Controller
{
    private $discordToken; // Discord bot token
    private $channelId; // Channel ID
    private $updateChannelId; // Channel ID
    private $serverId; // Server ID
    private $headers;
    private $mrRobotId;

    public function __construct()
    {
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

    private function sendBotMessage($content)
    {
        return Http::retry(3, 100)->withHeaders($this->headers)->post("https://discord.com/api/v10/channels/{$this->channelId}/messages", [
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

        $response = $this->sendRequest('GET', "/channels/{$this->channelId}/messages");
        
        $processedMessages = Cache::get('processed_discord_messages', []);
        
        foreach ($response as $message) {
            if (!in_array($message['id'], $processedMessages)) {
                if ($message['author']['id'] !== $this->mrRobotId) {
                    $this->handleMrRobotMention($message, $ollamaService);
                }
                $processedMessages[] = $message['id'];
            }
        }

        $processedMessages = array_slice($processedMessages, -100);
        Cache::put('processed_discord_messages', $processedMessages, now()->addDay());

        return $response;
    }

    private function handleMrRobotMention($message, OllamaService $ollamaService)
    {
        if (strpos($message['content'], '<@' . $this->mrRobotId . '>') !== false) {
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
                    // Process the response
                    $response = $response['response'];
                    
                    // Limitar a resposta a 2000 caracteres (limite do Discord)
                    if (strlen($response) > 2000) {
                        $response = substr($response, 0, 1997) . '...';
                    }
                    
                    $this->sendBotMessage($response);
                    
                    Log::info('Resposta enviada', [
                        'message_id' => $message['id'],
                        'response' => $response
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
                $this->sendBotMessage('Desculpe, ocorreu um erro ao processar sua solicitação.');
                $this->notifyAdminOfError($e);
            }
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


    // login social
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
                Log::error('Discord API request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body()
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
}