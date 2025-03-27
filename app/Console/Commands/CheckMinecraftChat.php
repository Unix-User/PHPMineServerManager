<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Services\OllamaService;
use App\Http\Controllers\JsonApiReloadedController;

class CheckMinecraftChat extends Command
{
    protected $signature = 'minecraft:check-chat';
    protected $description = 'Exibe e verifica as últimas mensagens do chat do Minecraft';

    private const MAX_ANALYSIS_ATTEMPTS = 3;
    private const RETRY_DELAY_SECONDS = 2;
    private const GRAVITY_MIN = 1;
    private const GRAVITY_MAX = 10;
    private const MESSAGE_TIME_LIMIT = 300;

    public function handle(OllamaService $ollamaService)
    {
        try {
            $messages = $this->fetchChatMessages();
            $this->displayChatMessages($messages);

            $analysisData = $this->analyzeMessagesWithRetry($ollamaService, $messages);
            $this->processAnalysisResults($analysisData, $ollamaService);

        } catch (Exception $e) {
            $this->handleGlobalError($e);
        }
    }

    private function fetchChatMessages(): array
    {
        $response = app(JsonApiReloadedController::class)
            ->getLatestChatsWithLimit(new Request(['limit' => 100]));

        if (!isset($response->original[0]['success'])) {
            throw new Exception('Resposta inválida do servidor Minecraft: Formato inesperado');
        }

        $messages = $response->original[0]['success'];
        $currentTime = time();

        return array_filter($messages, function($message) use ($currentTime) {
            return ($currentTime - $message['time']) <= self::MESSAGE_TIME_LIMIT;
        });
    }

    private function displayChatMessages(array $messages): void
    {
        $this->info("<fg=cyan>Últimas mensagens do chat (últimos 5 minutos):</>");
        $this->newLine();

        $rows = array_map(function ($message) {
            return [
                date('H:i:s', $message['time'] ?? time()),
                $message['player'] ?? 'Desconhecido',
                $message['message'] ?? 'Mensagem inválida'
            ];
        }, $messages);

        $this->table(['Hora', 'Jogador', 'Mensagem'], $rows);
    }

    private function analyzeMessagesWithRetry(OllamaService $ollamaService, array $messages): array
    {
        $messagesForAnalysis = $this->prepareMessagesForAnalysis($messages);

        return retry(
            self::MAX_ANALYSIS_ATTEMPTS,
            fn() => $this->analyzeMessages($ollamaService, $messagesForAnalysis),
            self::RETRY_DELAY_SECONDS * 1000,
            fn($e) => $this->handleRetryError($e)
        );
    }

    private function prepareMessagesForAnalysis(array $messages): array
    {
        return array_values(array_filter(array_map(
            fn($m) => isset($m['player'], $m['message'], $m['time']) ? [
                'player' => $m['player'],
                'message' => $m['message'],
                'time' => $m['time']
            ] : null,
            $messages
        )));
    }

    private function analyzeMessages(OllamaService $ollamaService, array $messages): array
    {
        $response = $ollamaService->generate($this->buildAnalysisRequest($messages));

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
            if ($this->isValidJson($content)) {
                return json_decode($content, true);
            }

            $jsonContent = $this->extractJsonContent($content);
            $data = json_decode($jsonContent, true) ?? [];

            if (!isset($data['infracoes'])) {
                throw new Exception('Formato de resposta inválido: campo "infracoes" ausente');
            }

            return $data;
        } catch (Exception $e) {
            Log::error('Falha ao analisar resposta do Ollama', [
                'content' => $content,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Formato de resposta inválido: ' . $e->getMessage());
        }
    }

    private function isValidJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function extractJsonContent(string $content): string
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

    private function processAnalysisResults(array $analysisData, OllamaService $ollamaService): void
    {
        if (empty($analysisData['infracoes'])) {
            $this->info("<fg=green>✅ Checagem concluída. Nenhuma mensagem inadequada detectada.</>");
            return;
        }

        $this->displayViolations($analysisData['infracoes']);
        $this->sendPlayerWarnings($analysisData['infracoes'], $ollamaService);
    }

    private function displayViolations(array $violations): void
    {
        $this->error("<fg=red>=== RELATÓRIO DE LINGUAGEM INADEQUADA ===</>");

        foreach ($violations as $violation) {
            $this->line("<fg=red>[{$violation['jogador']}]: {$violation['mensagem']}</>");
            $this->line("<fg=magenta>Problemas: " . implode(', ', $violation['problemas']) . "</>");
            $this->line("<fg=blue>Gravidade: {$violation['gravidade']}</>");
            $this->newLine();
        }
    }

    private function sendPlayerWarnings(array $violations, OllamaService $ollamaService): void
    {
        $players = array_unique(array_column($violations, 'jogador'));

        foreach ($players as $player) {
            $messages = array_filter($violations, fn($v) => $v['jogador'] === $player);
            $this->sendWarningToPlayer($player, $messages, $ollamaService);
        }

        $this->info("<fg=green>✅ Alertas enviados para: " . implode(', ', $players) . "</>");
    }

    private function sendWarningToPlayer(string $player, array $violations, OllamaService $ollamaService): void
    {
        $message = $this->generateWarningMessage($ollamaService, $player, $violations);
        $lines = explode("\n", $message);
        $lines = array_filter($lines, function ($line) {
            return trim($line) !== '';
        });
        foreach ($lines as $line) {
            sleep(1);
            try {
                $this->sendChatMessage($player, "[§cStaff§r] Mr.Robot: " . trim($line));
                
            } catch (Exception $e) {
                Log::error("Falha ao enviar mensagem para {$player} (linha da advertência)", ['error' => $e, 'line' => trim($line)]);
                $this->error("<fg=red>Falha ao enviar uma linha da advertência para {$player}. Veja os logs para detalhes.</>");
            }
        }
    }

    private function generateWarningMessage(OllamaService $ollamaService, string $player, array $violations): string
    {
        $response = $ollamaService->generate([
            'model' => env('OLLAMA_MODEL', 'google_genai.gemini-2.0-flash-exp'),
            'messages' => [[
                'role' => 'user',
                'content' => $this->getWarningPrompt($player, $violations)
            ]],
            'options' => [
                'temperature' => 0.5,
                'max_tokens' => 200
            ]
        ]);

        return trim($response['choices'][0]['message']['content'] ?? '');
    }

    private function getWarningPrompt(string $player, array $violations): string
    {
        $messages = implode("\n", array_map(
            fn($v) => "- [{$v['timestamp']}] Gravidade {$v['gravidade']}: {$v['mensagem']}",
            $violations
        ));

        return <<<PROMPT
        Crie uma mensagem de advertência direta e educativa para o jogador {$player}, usando códigos de cor do Minecraft (§).
        IMPORTANTE:
        - NÃO inclua "Aqui está a mensagem de advertência formatada:" ou qualquer texto de sistema
        - A mensagem deve começar diretamente com o conteúdo formatado

        Regras de formatação:
        1. Seja conciso e objetivo
        2. Use cores para destacar:
           - Nome do jogador (vermelho claro)
           - Gravidade (vermelho escuro)
           - Avisos importantes (amarelo/dourado)
           - Texto geral (cinza claro)
        3. Formato: § + código (ex: §c para vermelho)
        4. Conteúdo:
           - Título de AVISO
           - Nome do jogador
           - Lista de infrações com timestamps
           - Consequências de reincidência
           - Instrução para revisar as regras em minecraft.udianix.com.br/rules
        5. Infrações de {$player}:
        {$messages}
        PROMPT;
    }

    private function sendChatMessage(string $player, string $message): void
    {
        $response = app(JsonApiReloadedController::class)->sendMessage(
            new Request(['playerName' => $player, 'message' => $message])
        );

        if (!isset($response->original['success'])) {
            throw new Exception("Falha ao enviar mensagem para {$player}");
        }
    }


    private function handleGlobalError(Exception $e): void
    {
        Log::error('Erro no processo de verificação de chat', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        $this->error("<fg=red>Erro:</> {$e->getMessage()}");
    }

    private function handleRetryError(Exception $e): void
    {
        Log::warning("Tentativa de análise falhou: {$e->getMessage()}");
        $this->warn("Erro na análise: {$e->getMessage()}");
    }
}