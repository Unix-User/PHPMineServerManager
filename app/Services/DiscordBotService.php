<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DiscordBotService
{
    protected $client;
    protected $botToken;
    protected $botId;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://discord.com/api/v10/',
            'headers' => [
                'Authorization' => 'Bot ' . config('services.discord.bot_token'),
                'Content-Type' => 'application/json',
            ]
        ]);

        $this->botToken = config('services.discord.bot_token');
        $this->botId = config('services.discord.bot_id');
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

    protected function createDirectMessageChannel($recipientId)
    {
        $response = $this->client->post('users/@me/channels', [
            'json' => [
                'recipient_id' => $recipientId
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function sendMessage($channelId, $content)
    {
        try {
            $this->client->post("channels/{$channelId}/messages", [
                'json' => [
                    'content' => $content
                ]
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
                // Process the message as needed
                $processedMessages[] = $message['id'];
            }
        }

        Cache::put('processed_discord_messages_' . $channelId, $processedMessages, now()->addDay());
    }
}