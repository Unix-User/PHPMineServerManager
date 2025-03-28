<?php

namespace App\Services;

use App\Services\OllamaService;
use Exception;
use Illuminate\Support\Facades\Log;

class WarningMessageService
{
    private OllamaService $ollamaService;
    private MinecraftChatService $minecraftChatService;
    private DiscordMessageService $discordMessageService; // Adicionado DiscordMessageService
    private const MINECRAFT_WARNING_MESSAGE_PREFIX = "[§cStaff§r] Mr.Robot: ";

    public function __construct(OllamaService $ollamaService, MinecraftChatService $minecraftChatService, DiscordMessageService $discordMessageService) // Adicionado DiscordMessageService no construtor
    {
        $this->ollamaService = $ollamaService;
        $this->minecraftChatService = $minecraftChatService;
        $this->discordMessageService = $discordMessageService; // Injeta DiscordMessageService
    }

    public function sendPlayerWarnings(array $violations): void
    {
        $players = array_unique(array_column($violations, 'jogador'));

        foreach ($players as $player) {
            $playerViolations = array_filter($violations, fn($v) => $v['jogador'] === $player);
            $platform = $playerViolations[0]['platform'] ?? 'minecraft'; // Detecta a plataforma, padrão para minecraft se não especificado
            $this->sendWarningToPlayer($player, $playerViolations, $platform); // Passa a plataforma para sendWarningToPlayer
        }
    }

    private function sendWarningToPlayer(string $player, array $violations, string $platform): void
    {
        $warningMessage = $this->generateWarningMessage($player, $violations, $platform); // Passa a plataforma para generateWarningMessage
        $messageLines = explode("\n", $warningMessage);
        $filteredLines = array_filter($messageLines, fn ($line) => trim($line) !== '');

        foreach ($filteredLines as $line) {
            sleep(1); // Pausa para evitar flood no chat do Minecraft e possível rate limiting
            $this->sendMessageLine($player, trim($line), $platform); // Passa a plataforma para sendMessageLine
        }
    }

    private function sendMessageLine(string $player, string $line, string $platform): void
    {
        try {
            if ($platform === 'minecraft') {
                $this->minecraftChatService->sendChatMessage($player, self::MINECRAFT_WARNING_MESSAGE_PREFIX . $line);
            } elseif ($platform === 'discord') {
                $this->discordMessageService->sendChatMessage("[**{$player}**] " . $line); // Envia para o Discord, formatação em Markdown, menciona o jogador
            }
        } catch (Exception $e) {
            Log::error("Falha ao enviar linha da advertência para {$player} em {$platform}", ['error' => $e->getMessage(), 'line' => $line, 'platform' => $platform]);
        }
    }


    private function generateWarningMessage(string $player, array $violations, string $platform): string
    {
        $prompt = $this->getWarningPrompt($player, $violations, $platform); // Passa a plataforma para getWarningPrompt
        $response = $this->ollamaService->generate([
            'model' => env('OLLAMA_MODEL', 'google_genai.gemini-2.0-flash-exp'),
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]],
            'options' => [
                'temperature' => 0.5,
                'max_tokens' => 200
            ]
        ]);

        return trim($response['choices'][0]['message']['content'] ?? '');
    }

    private function getWarningPrompt(string $player, array $violations, string $platform): string
    {
        $messages = implode("\n", array_map(
            fn($v) => "- [{$v['timestamp']}] Gravidade {$v['gravidade']}: {$v['mensagem']}",
            $violations
        ));

        if ($platform === 'minecraft') {
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
        } elseif ($platform === 'discord') {
            return <<<PROMPT
            Crie uma mensagem de advertência direta e educativa para o usuário do Discord **{$player}**, usando formatação Markdown.
            IMPORTANTE:
            - NÃO inclua "Aqui está a mensagem de advertência formatada:" ou qualquer texto de sistema
            - A mensagem deve começar diretamente com o conteúdo formatado

            Regras de formatação Markdown:
            1. Seja conciso e objetivo
            2. Use Markdown para destacar:
               - Nome do jogador (negrito)
               - Gravidade (itálico ou negrito)
               - Avisos importantes (negrito/itálico)
            3. Conteúdo:
               - Título de **AVISO**
               - Nome do jogador em **negrito**
               - Lista de infrações com timestamps
               - Consequências de reincidência
               - Instrução para revisar as regras (se aplicável, ou link geral do discord)
            4. Infrações de **{$player}**:
            {$messages}
            PROMPT;
        }

        return ''; // Retorno padrão, embora não deva ser alcançado
    }
}