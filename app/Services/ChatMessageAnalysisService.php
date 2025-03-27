<?php

namespace App\Services;

use App\Services\OllamaService;
use Exception;
use Illuminate\Support\Facades\Log;

class ChatMessageAnalysisService
{
    private OllamaService $ollamaService;
    private const MAX_ANALYSIS_ATTEMPTS = 3;
    private const RETRY_DELAY_SECONDS = 2;
    private const GRAVITY_MIN = 1;
    private const GRAVITY_MAX = 10;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    public function analyzeMessagesWithRetry(array $messages): array
    {
        $messagesForAnalysis = $this->prepareMessagesForAnalysis($messages);

        return retry(
            self::MAX_ANALYSIS_ATTEMPTS,
            fn() => $this->analyzeMessages($messagesForAnalysis),
            self::RETRY_DELAY_SECONDS * 1000,
            fn($e) => $this->handleRetryError($e)
        );
    }

    private function prepareMessagesForAnalysis(array $messages): array
    {
        $validMessages = [];
        foreach ($messages as $message) {
            if (isset($message['player'], $message['message'], $message['time'])) {
                $validMessages[] = [
                    'player' => $message['player'],
                    'message' => $message['message'],
                    'time' => $message['time']
                ];
            }
        }
        return $validMessages;
    }

    public function analyzeMessages(array $messages): array
    {
        $response = $this->ollamaService->generate($this->buildAnalysisRequest($messages));

        if (!isset($response['choices'][0]['message']['content'])) {
            throw new Exception('Resposta do Ollama em formato inesperado');
        }

        $analysisData = $this->parseAnalysisResponse($response);

        $this->validateAnalysisData($analysisData);
        return $analysisData;
    }

    private function buildAnalysisRequest(array $messages): array
    {
        return [
            'model' => env('OLLAMA_MODEL', 'google_genai.gemini-2.0-flash-exp'),
            'messages' => [[
                'role' => 'user',
                'content' => $this->getAnalysisPrompt($messages)
            ]],
            'format' => 'json',
            'options' => [
                'temperature' => 0.7,
                'max_tokens' => 1000
            ]
        ];
    }

    private function getAnalysisPrompt(array $messages): string
    {
        $formattedMessages = array_map(function($message) {
            return [
                'player' => $message['player'],
                'message' => $message['message'],
                'time' => date('H:i:s', $message['time'])
            ];
        }, $messages);

        return "Analise RIGOROSAMENTE as seguintes mensagens de chat e retorne um JSON válido com as infrações detectadas. O JSON deve seguir EXATAMENTE este formato:
        {
            \"infracoes\": [
                {
                    \"jogador\": \"NomeDoJogador\",
                    \"mensagem\": \"MensagemOriginal\",
                    \"timestamp\": \"HoraDaMensagem\",
                    \"problemas\": [\"Problema1\", \"Problema2\"],
                    \"gravidade\": 1-10,
                    \"categorias\": [\"Categoria1\", \"Categoria2\"]
                }
            ]
        }
        \nMensagens: " . json_encode($formattedMessages);
    }

    private function parseAnalysisResponse(array $response): array
    {
        $content = $response['choices'][0]['message']['content'] ?? '';

        try {
            $analysisData = $this->extractValidJson($content);
            $this->validateAnalysisData($analysisData);
            return $analysisData;
        } catch (Exception $e) {
            Log::error('Falha ao analisar resposta do Ollama', [
                'content' => $content,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Formato de resposta inválido: ' . $e->getMessage());
        }
    }

    private function extractValidJson(string $content): array
    {
        if ($this->isValidJson($content)) {
            return json_decode($content, true);
        }

        $jsonContent = $this->extractJsonContentFromString($content);
        $data = json_decode($jsonContent, true) ?? [];

        if (!isset($data['infracoes'])) {
            throw new Exception('Formato de resposta inválido: campo "infracoes" ausente');
        }

        return $data;
    }

    private function isValidJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function extractJsonContentFromString(string $content): string
    {
        preg_match('/```(?:json)?\s*(.*?)\s*```/s', $content, $matches);

        if (empty($matches[1])) {
            preg_match('/\{.*\}/s', $content, $matches);
        }

        if (empty($matches[1])) {
            throw new Exception('Formato de resposta inválido: JSON não encontrado');
        }

        return $matches[1];
    }

    private function validateAnalysisData(array $data): void
    {
        if (!isset($data['infracoes'])) {
            throw new Exception('Formato de análise inválido: campo "infracoes" ausente');
        }

        foreach ($data['infracoes'] as $infracao) {
            $this->validateInfraction($infracao);
        }
    }

    private function validateInfraction(array $infracao): void
    {
        $requiredFields = ['jogador', 'mensagem', 'timestamp', 'problemas', 'gravidade', 'categorias'];
        foreach ($requiredFields as $field) {
            if (!isset($infracao[$field])) {
                throw new Exception("Campo obrigatório '{$field}' ausente na infração.");
            }
        }

        if (!is_array($infracao['problemas']) || !is_array($infracao['categorias'])) {
            throw new Exception("Os campos 'problemas' e 'categorias' devem ser arrays");
        }

        if ($infracao['gravidade'] < self::GRAVITY_MIN || $infracao['gravidade'] > self::GRAVITY_MAX) {
            throw new Exception("Gravidade inválida: {$infracao['gravidade']}");
        }
    }

    private function handleRetryError(Exception $e): void
    {
        Log::warning("Tentativa de análise falhou: {$e->getMessage()}");
    }
}