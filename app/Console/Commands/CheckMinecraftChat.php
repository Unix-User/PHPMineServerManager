<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\{
    OllamaService,
    MinecraftChatService,
    ChatMessageAnalysisService,
    WarningMessageService
};

class CheckMinecraftChat extends Command
{
    protected $signature = 'minecraft:check-chat {--print}';
    protected $description = 'Exibe e verifica as últimas mensagens do chat do Minecraft';

    public function handle(
        OllamaService $ollamaService,
        MinecraftChatService $minecraftChatService,
        ChatMessageAnalysisService $chatMessageAnalysisService,
        WarningMessageService $warningMessageService
    ): void {
        try {
            $this->processChatMessages(
                $minecraftChatService,
                $chatMessageAnalysisService,
                $warningMessageService
            );
        } catch (Exception $e) {
            $this->handleGlobalError($e);
        }
    }

    private function processChatMessages(
        MinecraftChatService $minecraftChatService,
        ChatMessageAnalysisService $chatMessageAnalysisService,
        WarningMessageService $warningMessageService
    ): void {
        $messages = $minecraftChatService->fetchChatMessages();
        
        if ($this->option('print')) {
            $this->info("<fg=cyan>Últimas mensagens do chat (últimos 5 minutos):</>");
            $this->newLine();
            
            $rows = array_map(fn($message) => [
                date('H:i:s', $message['time'] ?? time()),
                $message['player'] ?? 'Desconhecido',
                $message['message'] ?? 'Mensagem inválida',
            ], $messages);
            
            $this->table(['Hora', 'Jogador', 'Mensagem'], $rows);
        }

        $chatAnalysisResult = $chatMessageAnalysisService->analyzeMessagesWithRetry($messages);
        $this->processAnalysisResults($chatAnalysisResult, $warningMessageService);
    }

    private function handleGlobalError(Exception $e): void {
        Log::error('Erro no processo de verificação de chat', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        $this->error("<fg=red>Erro:</> {$e->getMessage()}");
    }

    private function processAnalysisResults(array $chatAnalysisResult, WarningMessageService $warningMessageService): void {
        if (empty($chatAnalysisResult['infracoes'])) {
            if ($this->option('print')) {
                $this->info("<fg=green>✅ Checagem concluída. Nenhuma mensagem inadequada detectada.</>");
            }
        } else {
            if ($this->option('print')) {
                $this->info("<fg=yellow>⚠️  Infrações detectadas:</>");
                $this->newLine();
                
                $rows = array_map(fn($violation) => [
                    date('H:i:s', strtotime($violation['timestamp']) ?: time()),
                    $violation['jogador'] ?? 'Desconhecido',
                    $violation['mensagem'] ?? 'Mensagem inválida',
                    implode(', ', $violation['problemas'] ?? []),
                    implode(', ', $violation['categorias'] ?? []),
                    $violation['gravidade'] ?? 'N/A',
                ], $chatAnalysisResult['infracoes']);
                
                $this->table(['Hora', 'Jogador', 'Mensagem', 'Problemas', 'Categorias', 'Gravidade'], $rows);
            }
            $warningMessageService->sendPlayerWarnings($chatAnalysisResult['infracoes']);
        }
    }
}