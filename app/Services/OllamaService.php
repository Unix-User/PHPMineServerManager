<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.ollama.base_url', 'https://ollama.udianix.com.br/ollama');
        $this->apiKey = config('services.ollama.api_key');
    }

    public function generate($data)
    {
        $url = $this->baseUrl . '/chat/completions';

        Log::info('Sending request to Ollama', ['url' => $url, 'data' => $data]);

        try {
            $response = Http::retry(3, 1000) // Tenta 3 vezes com intervalo de 1 segundo
                ->timeout(60) // Aumenta o tempo limite para 60 segundos
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $data);

            Log::info('Received response from Ollama', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('Failed to get response from Ollama: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Exception in Ollama request', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}