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
        $this->baseUrl = env('OLLAMA_BASE_URL');
        $this->apiKey = env('OLLAMA_API_KEY');
        
        Log::info('OllamaService inicializado', [
            'baseUrl' => $this->baseUrl,
            'apiKey' => $this->apiKey ? '***' : 'Não configurado'
        ]);
    }

    public function generate($data)
    {
        $url = $this->baseUrl . '/chat/completions';

        Log::info('Preparando requisição para Ollama', [
            'url' => $url,
            'data' => $data,
            'model' => $data['model'] ?? 'Não especificado',
            'messages_count' => count($data['messages'] ?? [])
        ]);

        try {
            Log::debug('Configurando tentativas de requisição', [
                'retries' => 3,
                'timeout' => 60
            ]);

            $response = Http::retry(3, 1000)
                ->timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $data);

            Log::info('Resposta recebida do Ollama', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body_size' => strlen($response->body()),
                'response_time' => $response->handlerStats()['total_time'] ?? 'N/A'
            ]);

            if ($response->successful()) {
                $jsonResponse = $response->json();
                
                Log::debug('Resposta JSON do Ollama', [
                    'response_keys' => array_keys($jsonResponse),
                    'choices_count' => count($jsonResponse['choices'] ?? [])
                ]);
                
                return $jsonResponse;
            }

            $errorMessage = 'Falha ao obter resposta do Ollama: ' . $response->body();
            Log::error($errorMessage, [
                'status_code' => $response->status(),
                'error_body' => $response->body()
            ]);
            
            throw new \Exception($errorMessage);
        } catch (\Exception $e) {
            Log::error('Exceção na requisição ao Ollama', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}