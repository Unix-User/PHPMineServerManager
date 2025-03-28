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
    protected $signature = 'discord:check-chat {--print}';
    protected $description = 'Exibe e verifica as últimas mensagens do chat do Discord';

    public function handle(
        OllamaService $ollamaService,
        DiscordChatService $discordChatService,
        DiscordMessageService $DiscordMessageService, // Mudança: Usando DiscordMessageService
        WarningMessageService $warningMessageService // Adicionado WarningMessageService
    ): void {
        try {
            $this->processChatMessages(
                $discordChatService,
                $ollamaService, // Mudança: Usando OllamaService para análise
                $DiscordMessageService, // Mudança: Usando DiscordMessageService
                $warningMessageService // Adicionado WarningMessageService
            );
        } catch (Exception $e) {
            $this->handleGlobalError($e);
        }
    }

    private function processChatMessages(
        DiscordChatService $discordChatService,
        OllamaService $ollamaService, // Mudança: Usando OllamaService para análise
        DiscordMessageService $DiscordMessageService, // Mudança: Usando DiscordMessageService
        WarningMessageService $warningMessageService // Adicionado WarningMessageService
    ): void {
        $messages = $discordChatService->fetchChatMessages();
        $this->displayChatMessagesIfRequested($messages);

        $chatAnalysisResult = $this->analyzeMessagesWithOllama($ollamaService, $messages); // Mudança: Analisando com Ollama
        $this->processAnalysisResults($chatAnalysisResult, $DiscordMessageService, $warningMessageService); // Mudança: Processando resultados e enviando mensagem
    }

    private function analyzeMessagesWithOllama(OllamaService $ollamaService, array $messages): array
    {
        if (empty($messages)) {
            return ['infracoes' => []]; // Retorna sem infrações se não houver mensagens
        }

        $prompt = $this->createOllamaPrompt($messages);

        try {
            $ollamaResponse = $ollamaService->generate([
                'model' => env('OLLAMA_MODEL'),
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'format' => 'json'
            ]);

            if (isset($ollamaResponse['choices'][0]['message']['content'])) {
                $infracoes = json_decode($ollamaResponse['choices'][0]['message']['content'], true) ?? ['infracoes' => []];
                // Adiciona a plataforma 'discord' a cada infração
                if (isset($infracoes['infracoes'])) {
                    $infracoes['infracoes'] = array_map(function($infracao) {
                        $infracao['platform'] = 'discord';
                        return $infracao;
                    }, $infracoes['infracoes']);
                }
                return $infracoes;
            } else {
                Log::error('Resposta do Ollama sem conteúdo', ['response' => $ollamaResponse]);
                return ['infracoes' => []];
            }
        } catch (Exception $e) {
            Log::error('Erro ao analisar mensagens com Ollama', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return ['infracoes' => []];
        }
    }


    private function createOllamaPrompt(array $messages): string
    {
        $messagesText = json_encode(array_map(function ($msg) {
            return [
                'jogador' => $msg['author_username'] ?? 'Desconhecido',
                'mensagem' => $msg['content'] ?? 'Mensagem inválida',
                'timestamp' => $msg['timestamp'] ?? now()->toString(),
            ];
        }, $messages), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

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

        Mensagens: {$messagesText}";
    }


    private function displayChatMessagesIfRequested(array $messages): void
    {
        if ($this->option('print')) {
            $this->displayChatMessages($messages);
        }
    }

    private function displayChatMessages(array $messages): void
    {
        $this->info("<fg=cyan>Últimas mensagens do chat do Discord (últimos 5 minutos):</>");
        $this->newLine();

        $rows = array_map(fn($message) => [
            date('H:i:s', strtotime($message['timestamp']) ?: time()),
            $message['author_username'] ?? 'Desconhecido',
            $message['content'] ?? 'Mensagem inválida',
        ], $messages);

        $this->table(['Hora', 'Usuário', 'Mensagem'], $rows);
    }

    private function handleGlobalError(Exception $e): void
    {
        Log::error('Erro no processo de verificação de chat do Discord', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        $this->error("<fg=red>Erro:</> {$e->getMessage()}");
    }

    private function processAnalysisResults(array $chatAnalysisResult, DiscordMessageService $DiscordMessageService, WarningMessageService $warningMessageService): void // Mudança: Usando WarningMessageService
    {
        empty($chatAnalysisResult['infracoes'])
            ? $this->handleNoViolationsDetected($DiscordMessageService) // Mantém DiscordMessageService para no violations
            : $this->handleViolations($chatAnalysisResult['infracoes'], $warningMessageService); // Mudança: Usando WarningMessageService para violations
    }

    private function handleNoViolationsDetected(DiscordMessageService $DiscordMessageService): void // Mantém DiscordMessageService para no violations
    {
        if ($this->option('print')) {
            $this->info("<fg=green>✅ Checagem concluída. Nenhuma mensagem inadequada detectada no Discord.</>");
        }
        $DiscordMessageService->sendChatMessage("✅ Checagem de chat concluída. Nenhuma infração detectada."); // Mantém DiscordMessageService para no violations
    }

    private function handleViolations(array $violations, WarningMessageService $warningMessageService): void // Mudança: Usando WarningMessageService
    {
        $this->displayViolationsIfRequested($violations);
        // Remove a criação de mensagem e usa WarningMessageService para enviar as warnings
        $warningMessageService->sendPlayerWarnings($violations); // Usando WarningMessageService para enviar warnings
    }


    private function displayViolationsIfRequested(array $violations): void
    {
        if ($this->option('print')) {
            $this->displayViolations($violations);
        }
    }

    private function displayViolations(array $violations): void
    {
        $this->info("<fg=yellow>⚠️  Infrações detectadas no Discord:</>");
        $this->newLine();

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