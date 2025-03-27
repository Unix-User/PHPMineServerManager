<?php

namespace App\Services;

use App\Services\OllamaService;
use Exception;
use Illuminate\Support\Facades\Log;

class WarningMessageService
{
    private OllamaService $ollamaService;
    private MinecraftChatService $minecraftChatService;
    private const WARNING_MESSAGE_PREFIX = "[§cStaff§r] Mr.Robot: ";

    public function __construct(OllamaService $ollamaService, MinecraftChatService $minecraftChatService)
    {
        $this->ollamaService = $ollamaService;
        $this->minecraftChatService = $minecraftChatService;
    }

    public function sendPlayerWarnings(array $violations): void
    {
        $players = array_unique(array_column($violations, 'jogador'));

        foreach ($players as $player) {
            $playerViolations = array_filter($violations, fn($v) => $v['jogador'] === $player);
            $this->sendWarningToPlayer($player, $playerViolations);
        }
    }

    private function sendWarningToPlayer(string $player, array $violations): void
    {
        $warningMessage = $this->generateWarningMessage($player, $violations);
        $messageLines = explode("\n", $warningMessage);
        $filteredLines = array_filter($messageLines, fn ($line) => trim($line) !== '');

        foreach ($filteredLines as $line) {
            sleep(1); // Pausa para evitar flood no chat do Minecraft e possível rate limiting
            $this->sendMessageLine($player, trim($line));
        }
    }

    private function sendMessageLine(string $player, string $line): void
    {
        try {
            $this->minecraftChatService->sendChatMessage($player, self::WARNING_MESSAGE_PREFIX . $line);
        } catch (Exception $e) {
            Log::error("Falha ao enviar linha da advertência para {$player}", ['error' => $e->getMessage(), 'line' => $line]);
        }
    }


    private function generateWarningMessage(string $player, array $violations): string
    {
        $response = $this->ollamaService->generate([
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
}