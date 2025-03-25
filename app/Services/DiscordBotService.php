<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DiscordBotService
{
    protected $client;
    protected $channelId;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://discord.com/api/v10/',
            'headers' => [
                'Authorization' => 'Bot ' . config('services.discord.bot_token'),
                'Content-Type' => 'application/json',
            ]
        ]);
        $this->channelId = config('services.discord.channel_id');
    }

    public function handleDirectMessage($message)
    {
        try {
            $channel = $this->createDirectMessageChannel($message['author_id']);
            $this->sendMessage($channel['id'], 'Recebi sua mensagem: ' . $message['content']);
        } catch (\Exception $e) {
            Log::error('Erro ao processar mensagem do Discord: ' . $e->getMessage());
        }
    }

    public function createDirectMessageChannel($userId)
    {
        $response = Http::withHeaders($this->getHeaders())->post("https://discord.com/api/v10/users/{$userId}/channels", [
            'recipient_id' => $userId,
        ]);

        return $response->json();
    }

    private function getHeaders()
    {
        return [
            'Authorization' => 'Bot ' . config('services.discord.bot_token'),
            'Content-Type' => 'application/json',
        ];
    }

    public function sendMessage($channelId, $content)
    {
        try {
            $this->client->post("channels/{$channelId}/messages", [
                'json' => ['content' => $content]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem: ' . $e->getMessage());
        }
    }

    public function getChannelMessages($channelId)
    {
        $response = $this->client->get("channels/{$channelId}/messages");
        return json_decode($response->getBody(), true);
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
}
