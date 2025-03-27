<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\{
    OllamaService,
    DiscordChatService,
    ChatMessageAnalysisService,
    WarningMessageService
};

class CheckDiscordChat extends Command
{
    protected $signature = 'discord:check-chat {--print}';
    protected $description = 'Exibe e verifica as últimas mensagens do chat do Discord';

    public function handle(
        OllamaService $ollamaService,
        DiscordChatService $discordChatService,
        ChatMessageAnalysisService $chatMessageAnalysisService,
        WarningMessageService $warningMessageService
    ): void {
        try {
            $this->processChatMessages(
                $discordChatService,
                $chatMessageAnalysisService,
                $warningMessageService
            );
        } catch (Exception $e) {
            $this->handleGlobalError($e);
        }
    }

    private function processChatMessages(
        DiscordChatService $discordChatService,
        ChatMessageAnalysisService $chatMessageAnalysisService,
        WarningMessageService $warningMessageService
    ): void {
        $messages = $discordChatService->fetchChatMessages();
        $this->displayChatMessagesIfRequested($messages);

        $chatAnalysisResult = $chatMessageAnalysisService->analyzeMessagesWithRetry($messages);
        $this->processAnalysisResults($chatAnalysisResult, $warningMessageService);
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

    private function processAnalysisResults(array $chatAnalysisResult, WarningMessageService $warningMessageService): void
    {
        empty($chatAnalysisResult['infracoes'])
            ? $this->handleNoViolationsDetected()
            : $this->handleViolations($chatAnalysisResult['infracoes'], $warningMessageService);
    }

    private function handleNoViolationsDetected(): void
    {
        if ($this->option('print')) {
            $this->info("<fg=green>✅ Checagem concluída. Nenhuma mensagem inadequada detectada no Discord.</>");
        }
    }

    private function handleViolations(array $violations, WarningMessageService $warningMessageService): void
    {
        $this->displayViolationsIfRequested($violations);
        $warningMessageService->sendPlayerWarnings($violations);
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