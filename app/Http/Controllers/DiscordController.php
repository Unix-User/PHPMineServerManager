<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class DiscordController extends Controller
{
    private $discordToken; // Discord bot token
    private $channelId; // Channel ID
    private $updateChannelId; // Channel ID
    private $serverId; // Server ID
    private $headers;

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
    }

    public function sendMessage(Request $request, $content)
    {
        $webhookUrl = env('DISCORD_WEBHOOK_URL');
        $user = auth()->user();
        return Http::withHeaders($this->headers)->post($webhookUrl, [
            'content' => $content,
            'username' => $user->name,
            'avatar_url' => $user->profile_photo_url
        ]);
    }
    public function sendBotMessage(Request $request, $content)
    {
        $globalName = $request->user()->name;
        return Http::withHeaders($this->headers)->post("https://discord.com/api/v10/channels/{$this->channelId}/messages", [
            'content' => $content,
            'username' => auth()->user()->name, // Using Jetstream authenticated user's name
            'avatar' => auth()->user()->profile_photo_url
        ]);
    }

    public function getChannelMessages()
    {
        return $this->sendRequest('get', "https://discord.com/api/v10/channels/{$this->channelId}/messages");
    }

    public function getServerUpdates()
    {
        return $this->sendRequest('get', "https://discord.com/api/v10/channels/{$this->updateChannelId}/messages");
    }

    public function createRole(Request $request)
    {
        return $this->sendRequest('post', "https://discord.com/api/v10/guilds/{$this->serverId}/roles", ['name' => $request->input('name')]);
    }

    public function getRoles()
    {
        return $this->sendRequest('get', "https://discord.com/api/v10/guilds/{$this->serverId}/roles");
    }

    public function updateRole(Request $request, $roleId)
    {
        return $this->sendRequest('patch', "https://discord.com/api/v10/guilds/{$this->serverId}/roles/{$roleId}", ['name' => $request->input('name')]);
    }

    public function deleteRole($roleId)
    {
        return $this->sendRequest('delete', "https://discord.com/api/v10/guilds/{$this->serverId}/roles/{$roleId}");
    }

    public function assignRole($userId, $roleId)
    {
        return $this->sendRequest('put', "https://discord.com/api/v10/guilds/{$this->serverId}/members/{$userId}/roles/{$roleId}");
    }

    public function removeRole($userId, $roleId)
    {
        return $this->sendRequest('delete', "https://discord.com/api/v10/guilds/{$this->serverId}/members/{$userId}/roles/{$roleId}");
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



    private function sendRequest($method, $url, $data = [])
    {
        $response = Http::withHeaders($this->headers)->$method($url, $data);

        if ($response->failed()) {
            return ['message' => 'erro ao processar a solicitação', 'code' => $response->status()]; // Returns error message in Portuguese
        }

        return $response->json(); // Returns the API response (you can handle the data as needed)
    }
}
