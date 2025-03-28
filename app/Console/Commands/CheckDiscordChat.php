<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\{
    OllamaService,
    DiscordChatService,
    DiscordMessageService,
    WarningMessageService
};

class CheckDiscordChat extends Command
{
    protected $signature = 'discord:check-chat';
    protected $description = 'Exibe e verifica as últimas mensagens do chat do Discord';

    public function handle(
        OllamaService $ollamaService,
        DiscordChatService $discordChatService,
        DiscordMessageService $DiscordMessageService,
        WarningMessageService $warningMessageService
    ): void {
        Log::info('Iniciando execução do comando CheckDiscordChat');
        try {
            $this->info("Iniciando verificação do chat do Discord...");
            Log::info('Iniciando verificação do chat do Discord');
            $this->processChatMessages(
                $discordChatService,
                $ollamaService,
                $DiscordMessageService,
                $warningMessageService
            );
            Log::info('Verificação do chat do Discord concluída com sucesso');
        } catch (Exception $e) {
            Log::error('Erro durante a execução do comando CheckDiscordChat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->handleGlobalError($e);
        }
    }

    private function processChatMessages(
        DiscordChatService $discordChatService,
        OllamaService $ollamaService,
        DiscordMessageService $DiscordMessageService,
        WarningMessageService $warningMessageService
    ): void {
        Log::info('Iniciando processamento das mensagens do Discord');
        $this->info("Obtendo mensagens do Discord...");
        Log::info('Obtendo mensagens do Discord');
        $messages = $discordChatService->fetchChatMessages();
        Log::info('Mensagens obtidas do Discord', [
            'count' => count($messages),
            'sample_messages' => array_slice($messages, 0, 3)
        ]);
        $this->displayChatMessages($messages);

        $this->info("Analisando mensagens com Ollama...");
        Log::info('Iniciando análise das mensagens com Ollama', [
            'model' => env('OLLAMA_MODEL'),
            'messages_count' => count($messages)
        ]);
        $chatAnalysisResult = $this->analyzeMessagesWithOllama($ollamaService, $messages);
        Log::info('Processando resultados da análise');
        $this->processAnalysisResults($chatAnalysisResult, $DiscordMessageService, $warningMessageService);
    }

    private function analyzeMessagesWithOllama(OllamaService $ollamaService, array $messages): array
    {
        Log::debug('Verificando se há mensagens para análise');
        if (empty($messages)) {
            $this->info("Nenhuma mensagem encontrada para análise.");
            Log::info('Nenhuma mensagem encontrada para análise');
            return ['infracoes' => []];
        }

        $prompt = $this->createOllamaPrompt($messages);
        $this->info("Prompt enviado para Ollama: " . $prompt);
        Log::info('Prompt enviado para Ollama', [
            'prompt' => $prompt,
            'prompt_length' => strlen($prompt)
        ]);

        try {
            Log::debug('Preparando requisição para Ollama', [
                'model' => env('OLLAMA_MODEL'),
                'format' => 'json'
            ]);
            $ollamaResponse = $ollamaService->generate([
                'model' => env('OLLAMA_MODEL'),
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'format' => 'json'
            ]);

            Log::info('Resposta recebida do Ollama', [
                'response' => $ollamaResponse,
                'response_keys' => array_keys($ollamaResponse)
            ]);

            if (isset($ollamaResponse['choices'][0]['message']['content'])) {
                $infracoes = json_decode($ollamaResponse['choices'][0]['message']['content'], true) ?? ['infracoes' => []];
                if (isset($infracoes['infracoes'])) {
                    Log::debug('Processando infrações detectadas');
                    $infracoes['infracoes'] = array_map(function($infracao) {
                        $infracao['platform'] = 'discord';
                        return $infracao;
                    }, $infracoes['infracoes']);
                }
                $this->info("Análise concluída. Infrações encontradas: " . count($infracoes['infracoes']));
                Log::info('Análise concluída', [
                    'infracoes_count' => count($infracoes['infracoes']),
                    'sample_infracoes' => array_slice($infracoes['infracoes'], 0, 3)
                ]);
                return $infracoes;
            } else {
                $this->error("Resposta do Ollama sem conteúdo");
                Log::error('Resposta do Ollama sem conteúdo', [
                    'response' => $ollamaResponse,
                    'response_structure' => array_keys($ollamaResponse)
                ]);
                return ['infracoes' => []];
            }
        } catch (Exception $e) {
            $this->error("Erro ao analisar mensagens com Ollama: " . $e->getMessage());
            Log::error('Erro ao analisar mensagens com Ollama', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'messages_count' => count($messages)
            ]);
            return ['infracoes' => []];
        }
    }

    private function createOllamaPrompt(array $messages): string
    {
        Log::debug('Criando prompt para Ollama');
        $messagesText = json_encode(array_map(function ($msg) {
            return [
                'jogador' => $msg['author_username'] ?? 'Desconhecido',
                'mensagem' => $msg['content'] ?? 'Mensagem inválida',
                'timestamp' => $msg['timestamp'] ?? now()->toString(),
            ];
        }, $messages), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $prompt = "Analise RIGOROSAMENTE as seguintes mensagens de chat e retorne um JSON válido com as infrações detectadas. O JSON deve seguir EXATAMENTE este formato:
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

        Mensagens: {$messagesText}";

        Log::debug('Prompt criado', ['prompt_length' => strlen($prompt)]);
        return $prompt;
    }

    private function displayChatMessages(array $messages): void
    {
        Log::info('Exibindo mensagens do chat do Discord', [
            'count' => count($messages),
            'sample_messages' => array_slice($messages, 0, 3)
        ]);
        $this->info("<fg=cyan>Últimas mensagens do chat do Discord (últimos 5 minutos):</>");
        $this->newLine();

        $rows = array_map(fn($message) => [
            date('H:i:s', strtotime($message['timestamp']) ?: time()),
            $message['author_username'] ?? 'Desconhecido',
            $message['content'] ?? 'Mensagem inválida',
        ], $messages);

        $this->table(['Hora', 'Usuário', 'Mensagem'], $rows);
        Log::debug('Mensagens exibidas na tabela');
    }

    private function handleGlobalError(Exception $e): void
    {
        Log::error('Erro global no comando CheckDiscordChat', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $this->error("<fg=red>Erro global:</> {$e->getMessage()}");
    }

    private function processAnalysisResults(array $chatAnalysisResult, DiscordMessageService $DiscordMessageService, WarningMessageService $warningMessageService): void
    {
        Log::info('Processando resultados da análise', [
            'has_infracoes' => !empty($chatAnalysisResult['infracoes']),
            'infracoes_count' => count($chatAnalysisResult['infracoes'] ?? [])
        ]);
        empty($chatAnalysisResult['infracoes'])
            ? $this->handleNoViolationsDetected($DiscordMessageService)
            : $this->handleViolations($chatAnalysisResult['infracoes'], $warningMessageService);
    }

    private function handleNoViolationsDetected(DiscordMessageService $DiscordMessageService): void
    {
        $this->info("<fg=green>✅ Checagem concluída. Nenhuma mensagem inadequada detectada no Discord.</>");
        Log::info('Checagem de chat do Discord concluída. Nenhuma infração detectada.');
    }

    private function handleViolations(array $violations, WarningMessageService $warningMessageService): void
    {
        $this->displayViolations($violations);
        $this->info("Enviando advertências para os jogadores...");
        Log::info('Enviando advertências para os jogadores', ['violations_count' => count($violations)]);
        $warningMessageService->sendPlayerWarnings($violations);
    }

    private function displayViolations(array $violations): void
    {
        $this->info("<fg=yellow>⚠️  Infrações detectadas no Discord:</>");
        $this->newLine();
        Log::info('Exibindo infrações detectadas', ['violations_count' => count($violations)]);

        $rows = array_map(fn($violation) => [
            date('H:i:s', strtotime($violation['timestamp']) ?: time()),
            $violation['jogador'] ?? 'Desconhecido',
            $violation['mensagem'] ?? 'Mensagem inválida',
            implode(', ', $violation['problemas'] ?? []),
            implode(', ', $violation['categorias'] ?? []),
            $violation['gravidade'] ?? 'N/A',
        ], $violations);

        $this->table(['Hora', 'Usuário', 'Mensagem', 'Problemas', 'Categorias', 'Gravidade'], $rows);
    }
}