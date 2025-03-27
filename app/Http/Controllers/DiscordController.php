<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use App\Services\OllamaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\DiscordBotService;
use Illuminate\Support\Arr;

class DiscordController extends Controller
{
    private $discordToken;
    private $channelId;
    private $updateChannelId;
    private $serverId;
    private $headers;
    private $mrRobotId;
    protected $discordService;

    /**
     * Construtor da classe, inicializa as configurações do Discord.
     * Define tokens, channel IDs, server ID, headers para autenticação na API do Discord e o ID do bot Mr. Robot.
     * @param DiscordBotService $discordService Serviço para interagir com a API do Discord.
     */
    public function __construct(DiscordBotService $discordService)
    {
        $this->discordService = $discordService;
        $this->discordToken = env('DISCORD_TOKEN');
        $this->channelId = env('DISCORD_CHANNEL_ID');
        $this->updateChannelId = env('DISCORD_UPDATE_CHANNEL_ID');
        $this->serverId = env('DISCORD_SERVER_ID');
        $this->headers = [
            'Authorization' => 'Bot ' . $this->discordToken,
            'Content-Type' => 'application/json',
        ];
        $this->mrRobotId = env('MR_ROBOT_ID', '1140237518978166896');
    }

    /**
     * Envia uma mensagem para o canal do Discord via webhook.
     * Utiliza um webhook configurado para enviar mensagens para o Discord, permitindo enviar mensagens como um usuário autenticado.
     * @param Request $request Objeto da requisição HTTP.
     * @param string $content Conteúdo da mensagem a ser enviada.
     * @param OllamaService $ollamaService Serviço de IA para processar a mensagem e gerar uma resposta do bot.
     * @return \Illuminate\Http\Client\Response Resposta da API do Discord após tentar enviar a mensagem.
     */
    public function sendMessage(Request $request, $content, OllamaService $ollamaService)
    {
        $webhookUrl = env('DISCORD_WEBHOOK_URL');
        $user = auth()->user();
        $response = Http::withHeaders($this->headers)->post($webhookUrl, [
            'content' => $content,
            'username' => $user->name,
            'avatar_url' => $user->profile_photo_url
        ]);

        if ($response->successful()) {
            $this->handleBotResponse($content, $ollamaService);
        }

        return $response;
    }

    /**
     * Processa a resposta do bot usando o serviço de IA.
     * Gera uma resposta usando o OllamaService com base no conteúdo da mensagem recebida e envia essa resposta de volta para o canal do Discord.
     * Em caso de erro na geração da resposta, notifica o administrador.
     * @param string $content Conteúdo da mensagem original para a qual o bot deve responder.
     * @param OllamaService $ollamaService Serviço de IA para gerar a resposta do bot.
     */
    private function handleBotResponse($content, OllamaService $ollamaService)
    {
        try {
            $prompt = "Você é Mr. Robot, um assistente de IA amigável. Responda à seguinte mensagem: {$content}";
            $ollama_response = $ollamaService->generate([
                'model' => env('OLLAMA_MODEL', 'llama3.2'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'stream' => false,
                'system' => "You are Mr. Robot, a friendly AI assistant."
            ]);
            
            $this->sendBotMessage($ollama_response['response']);
            Log::info('Resposta do bot enviada', ['response' => $ollama_response['response']]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar resposta do bot', ['error' => $e->getMessage(), 'content' => $content]);
            $this->notifyAdminOfError($e);
        }
    }

    /**
     * Envia uma mensagem como o bot para um canal específico no Discord.
     * Utiliza o token do bot para autenticar e enviar a mensagem para o canal especificado ou para o canal padrão se nenhum for especificado.
     * @param string $content Conteúdo da mensagem a ser enviada pelo bot.
     * @param string|null $channelId ID do canal do Discord para enviar a mensagem. Se null, usa o canal padrão.
     * @return \Illuminate\Http\Client\Response Resposta da API do Discord após tentar enviar a mensagem.
     */
    private function sendBotMessage($content, $channelId = null)
    {
        $channel = $channelId ?? $this->channelId;
        return Http::retry(3, 100)->withHeaders($this->headers)->post("https://discord.com/api/v10/channels/{$channel}/messages", [
            'content' => $content,
        ]);
    }

    /**
     * Obtém e processa as mensagens do canal principal do Discord.
     * Verifica o limite de taxa para evitar sobrecarregar a API do Discord, obtém as mensagens do canal e as processa para responder a menções ao bot.
     * @param OllamaService $ollamaService Serviço de IA para processar as mensagens e gerar respostas.
     */
    public function getChannelMessages(OllamaService $ollamaService)
    {
        if (RateLimiter::tooManyAttempts('check_discord_messages', 60)) {
            Log::warning('Rate limit exceeded for Discord API calls');
            return;
        }

        RateLimiter::hit('check_discord_messages');

        $this->processChannelMessages($this->channelId, $ollamaService);
        $this->processDirectMessages($ollamaService);
    }

    /**
     * Obtém e processa mensagens diretas (DMs) enviadas ao bot.
     * Similar ao `getChannelMessages`, mas focado em mensagens diretas. Cria um canal de DM com o bot (se não existir), obtém as mensagens e responde a elas.
     * @param OllamaService $ollamaService Serviço de IA para processar as mensagens de DM e gerar respostas.
     */
    private function processDirectMessages(OllamaService $ollamaService)
    {
        if (RateLimiter::tooManyAttempts('check_discord_dm', 60)) {
            Log::warning('Rate limit exceeded for Discord API calls (DMs)');
            return;
        }

        RateLimiter::hit('check_discord_dm');

        $dmChannel = $this->discordService->createDirectMessageChannel($this->mrRobotId);

        if (isset($dmChannel['type']) && $dmChannel['type'] === 1) {
            $this->processMessages($dmChannel['id'], $ollamaService, 'processed_discord_dm_messages');
        }
    }

    /**
     * Processa as mensagens de um canal específico do Discord.
     * Obtém as mensagens do canal, verifica se já foram processadas (usando cache) e, se não, verifica se mencionam o bot para então gerar uma resposta adequada.
     * @param string $channelId ID do canal do Discord cujas mensagens serão processadas.
     * @param OllamaService $ollamaService Serviço de IA para processar as mensagens e gerar respostas.
     * @param string $cacheKey Chave para o cache de mensagens processadas, permitindo diferenciar caches para canais e DMs.
     */
    private function processMessages($channelId, OllamaService $ollamaService, $cacheKey)
    {
        $response = $this->sendRequest('GET', "/channels/{$channelId}/messages");

        // Verifica se a resposta da API do Discord indica um erro.
        if (is_array($response) && isset($response['message'])) {
            Log::error('Erro ao obter mensagens do canal', [
                'channelId' => $channelId,
                'error' => $response['message'],
                'code' => $response['code'] ?? 'N/A'
            ]);
            return; // Retorna para evitar processamento de uma resposta de erro.
        }

        // Garante que a resposta é um array antes de processar. Se não for, loga um erro e retorna.
        if (!is_array($response)) {
            Log::error('Resposta inesperada da API do Discord ao obter mensagens do canal', [
                'channelId' => $channelId,
                'response' => $response
            ]);
            return;
        }


        $processedMessages = Cache::get($cacheKey . '_' . $channelId, []);
        $newProcessedMessages = $processedMessages; // Use a new variable to track messages to be added

        foreach ($response as $message) {
            if (!in_array($message['id'], $processedMessages) && $message['author']['id'] !== $this->mrRobotId) {
                if (strpos($message['content'], '<@' . $this->mrRobotId . '>') !== false) {
                    $this->handleBotInteraction($message, $ollamaService);
                }
                $newProcessedMessages[] = $message['id']; // Add message ID to the new array
            }
        }

        // Mantém apenas os últimos 100 IDs de mensagens processadas para evitar estouro do cache e para performance.
        $newProcessedMessages = array_slice($newProcessedMessages, -100);
        if ($newProcessedMessages !== $processedMessages) { // Update cache only if there are new messages
            Cache::put($cacheKey . '_' . $channelId, $newProcessedMessages, now()->addDay());
        }
    }

    /**
     * Processa as mensagens do canal principal do Discord.
     * @param string $channelId ID do canal do Discord cujas mensagens serão processadas.
     * @param OllamaService $ollamaService Serviço de IA para processar as mensagens e gerar respostas.
     */
    private function processChannelMessages($channelId, OllamaService $ollamaService)
    {
        $this->processMessages($channelId, $ollamaService, 'processed_discord_messages');
    }


    /**
     * Processa respostas quando o bot é mencionado em uma mensagem ou recebe uma mensagem direta.
     * Extrai o conteúdo da mensagem, gera uma resposta usando OllamaService configurado para o bot Mr. Robot, e envia a resposta de volta para o canal.
     * @param array $message Dados da mensagem que mencionou o bot ou foi enviada diretamente.
     * @param OllamaService $ollamaService Serviço de IA para gerar a resposta do bot.
     */
    private function handleBotInteraction($message, OllamaService $ollamaService)
    {
        Log::info('Interação com o bot', [
            'message_id' => $message['id'],
            'author' => $message['author']['username'],
            'content' => $message['content']
        ]);

        try {
            $prompt = "Você é Mr. Robot, um assistente de IA amigável. Responda à seguinte mensagem: {$message['content']}";
            $response = $ollamaService->generate([
                'model' => env('OLLAMA_MODEL', 'llama3.2'),
                'prompt' => $prompt,
                'stream' => false,
                'system' => "You are Mr. Robot, a friendly AI assistant."
            ]);

            if (isset($response['response'])) {
                $this->sendBotMessage($response['response'], $message['channel_id']); // Use message's channel_id for response
                Log::info('Resposta enviada', [
                    'message_id' => $message['id'],
                    'response' => $response['response']
                ]);
            } else {
                throw new \Exception("Resposta do Ollama não contém a chave 'response'");
            }
        } catch (\Exception $e) {
            Log::error('Erro ao gerar resposta', [
                'message_id' => $message['id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'message_content' => $message['content'] // Adicionado content da mensagem original ao log de erro
            ]);
            $this->sendBotMessage('Desculpe, ocorreu um erro ao processar sua solicitação.', $message['channel_id']); // Use message's channel_id for error message
            $this->notifyAdminOfError($e);
        }
    }


    /**
     * Obtém as atualizações do servidor do Discord.
     * Recupera as mensagens do canal de atualizações do servidor, útil para logs ou informações importantes do servidor.
     * @return array|mixed Resposta da API do Discord contendo as mensagens do canal de atualizações ou uma mensagem de erro.
     */
    public function getServerUpdates()
    {
        return $this->sendRequest('GET', "/channels/{$this->updateChannelId}/messages");
    }

    /**
     * Cria um novo role no servidor do Discord.
     * Utiliza a API do Discord para criar um novo role com o nome especificado na requisição.
     * @param Request $request Requisição HTTP contendo o nome do role a ser criado.
     * @return array|mixed Resposta da API do Discord após tentar criar o role ou uma mensagem de erro.
     */
    public function createRole(Request $request)
    {
        return $this->sendRequest('POST', "/guilds/{$this->serverId}/roles", ['name' => $request->input('name')]);
    }

    /**
     * Obtém a lista de roles do servidor do Discord.
     * Recupera todos os roles configurados no servidor do Discord.
     * @return array|mixed Resposta da API do Discord contendo a lista de roles ou uma mensagem de erro.
     */
    public function getRoles()
    {
        return $this->sendRequest('GET', "/guilds/{$this->serverId}/roles");
    }

    /**
     * Atualiza um role existente no servidor do Discord.
     * Modifica o nome de um role específico no servidor do Discord.
     * @param Request $request Requisição HTTP contendo o novo nome do role.
     * @param string $roleId ID do role a ser atualizado.
     * @return array|mixed Resposta da API do Discord após tentar atualizar o role ou uma mensagem de erro.
     */
    public function updateRole(Request $request, $roleId)
    {
        return $this->sendRequest('PATCH', "/guilds/{$this->serverId}/roles/{$roleId}", ['name' => $request->input('name')]);
    }

    /**
     * Deleta um role do servidor do Discord.
     * Remove um role específico do servidor do Discord.
     * @param string $roleId ID do role a ser deletado.
     * @return array|mixed Resposta da API do Discord após tentar deletar o role ou uma mensagem de erro.
     */
    public function deleteRole($roleId)
    {
        return $this->sendRequest('DELETE', "/guilds/{$this->serverId}/roles/{$roleId}");
    }

    /**
     * Atribui um role a um usuário no servidor do Discord.
     * Adiciona um role a um membro específico do servidor do Discord.
     * @param string $userId ID do usuário a quem o role será atribuído.
     * @param string $roleId ID do role a ser atribuído.
     * @return array|mixed Resposta da API do Discord após tentar atribuir o role ou uma mensagem de erro.
     */
    public function assignRole($userId, $roleId)
    {
        return $this->sendRequest('PUT', "/guilds/{$this->serverId}/members/{$userId}/roles/{$roleId}");
    }

    /**
     * Remove um role de um usuário no servidor do Discord.
     * Remove um role de um membro específico do servidor do Discord.
     * @param string $userId ID do usuário de quem o role será removido.
     * @param string $roleId ID do role a ser removido.
     * @return array|mixed Resposta da API do Discord após tentar remover o role ou uma mensagem de erro.
     */
    public function removeRole($userId, $roleId)
    {
        return $this->sendRequest('DELETE', "/guilds/{$this->serverId}/members/{$userId}/roles/{$roleId}");
    }

    /**
     * Redireciona o usuário para a página de autenticação do Discord.
     * Inicia o fluxo de autenticação OAuth2 com o Discord, redirecionando o usuário para autorizar o aplicativo.
     * @return \Symfony\Component\HttpFoundation\RedirectResponse Redirecionamento para a página de autorização do Discord.
     */
    public function redirectToDiscord()
    {
        return Socialite::driver('discord')->redirect();
    }

    /**
     * Processa o callback de autenticação do Discord.
     * Após o usuário autorizar o aplicativo no Discord, este método é chamado para processar a resposta e obter os dados do usuário.
     * Atualmente, apenas obtém o usuário autenticado, mas pode ser expandido para salvar tokens, etc.
     */
    public function handleDiscordCallback()
    {
        $user = Socialite::driver('discord')->user();
        // $user->token
    }

    /**
     * Envia uma requisição para a API do Discord.
     * Método genérico para enviar requisições (GET, POST, PATCH, DELETE, PUT) para a API do Discord, lidando com autenticação e tratamento de erros.
     * @param string $method Método HTTP a ser usado (GET, POST, etc.).
     * @param string $endpoint Endpoint da API do Discord a ser acessado.
     * @param array $data Dados a serem enviados no corpo da requisição (para métodos POST, PATCH, PUT).
     * @return array|mixed Resposta JSON da API do Discord em caso de sucesso ou um array contendo mensagem de erro e código de status em caso de falha.
     */
    private function sendRequest($method, $endpoint, $data = [])
    {
        $baseUrl = 'https://discord.com/api/v10';
        $url = $baseUrl . $endpoint;

        try {
            $response = Http::withHeaders($this->headers)->$method($url, $data);
            $response->throw(); // Throw an exception on client or server error

            return $response->json();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Discord API request failed', [
                'url' => $url,
                'status' => $e->response->status(),
                'body' => $e->response->body(),
                'discord_error' => $e->response->json()
            ]);
            return ['message' => 'Erro ao processar a solicitação', 'code' => $e->response->status()];
        } catch (\Exception $e) {
            Log::error('Exception in Discord API request', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return ['message' => 'Erro ao processar a solicitação', 'code' => 500];
        }
    }

    /**
     * Notifica os administradores sobre erros críticos.
     * Método placeholder para implementar a lógica de notificação de administradores sobre exceções ou erros críticos na aplicação.
     * Atualmente não implementado, necessita lógica de notificação (e-mail, Slack, etc.).
     * @param \Exception $e Exceção que ocorreu e que precisa ser notificada aos administradores.
     */
    private function notifyAdminOfError(\Exception $e)
    {
        // TODO: Implementar a lógica para notificar os administradores sobre erros críticos
        // Pode ser via e-mail, Slack, ou outro método de sua escolha
        Log::critical('Erro crítico detectado, administrador precisa ser notificado', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }

    /**
     * Processa webhooks recebidos do Discord.
     * Endpoint para receber e processar eventos enviados pelo Discord via webhook, como interações de comandos, componentes de mensagens, etc.
     * @param Request $request Requisição HTTP contendo o payload do webhook do Discord.
     * @return \Illuminate\Http\JsonResponse Resposta JSON indicando o status do processamento do webhook.
     */
    public function handleWebhook(Request $request)
    {
        $signature = $request->header('X-Discord-Signature');
        $payload = $request->getContent();

        // **SEGURANÇA CRÍTICA**: A verificação da assinatura do webhook é essencial para garantir
        // que a requisição realmente veio do Discord e não foi falsificada.
        // A função `verifyWebhookSignature` precisa ser implementada com urgência.
        if (!$this->verifyWebhookSignature($signature, $payload)) {
            Log::warning('Webhook request não pôde ser verificada. Assinatura inválida.');
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401); // Retorna 401 Não Autorizado
        }


        $payload = $request->all();

        if (!isset($payload['type'])) {
            Log::error('Payload do webhook não contém o tipo', ['payload' => $payload]);
            return response()->json(['status' => 'error', 'message' => 'Invalid payload type'], 400);
        }

        switch ($payload['type']) {
            case 1:
                return response()->json(['type' => 1]); // Resposta a PING

            case 2:
                return $this->handleApplicationCommand($payload); // Processa comandos de aplicação

            case 3:
                return $this->handleMessageComponent($payload); // Processa componentes de mensagem

            default:
                Log::warning('Tipo de payload de webhook desconhecido', ['type' => $payload['type']]);
                return response()->json(['status' => 'warning', 'message' => 'Unknown payload type'], 200); // Responde com 200 OK para tipos desconhecidos para evitar retries desnecessários do Discord.
        }
    }

    /**
     * Verifica a assinatura do webhook do Discord.
     * **ATENÇÃO: A VERIFICAÇÃO DA ASSINATURA DO WEBHOOK NÃO FOI IMPLEMENTADA. ISSO É CRÍTICO PARA A SEGURANÇA.**
     * Necessita implementação da lógica de verificação de assinatura usando a chave pública do Discord.
     * @param string $signature Assinatura fornecida no header 'X-Discord-Signature'.
     * @param string $payload Conteúdo do payload da requisição webhook.
     * @return bool Retorna true se a assinatura for válida, false caso contrário.
     */
    protected function verifyWebhookSignature($signature, $payload): bool
    {
        // TODO: Implementar a verificação da assinatura do webhook usando a chave pública do Discord.
        // Documentação do Discord: https://discord.com/developers/docs/interactions/receiving-and-responding#security-and-authorization
        Log::warning('VERIFICAÇÃO DE ASSINATURA DO WEBHOOK NÃO IMPLEMENTADA. ISSO É UM RISCO DE SEGURANÇA!');
        return true; // Retorna true temporariamente para permitir o processamento. REMOVER EM PRODUÇÃO!
    }

    /**
     * Lida com comandos de aplicação recebidos via webhook.
     * Método placeholder para implementar a lógica de processamento de comandos de aplicação do Discord (slash commands).
     * **IMPLEMENTAÇÃO PENDENTE: Lógica para lidar com comandos de aplicação.**
     * Necessita implementação da lógica específica para cada comando suportado.
     * @param array $payload Payload do webhook contendo os dados do comando de aplicação.
     */
    protected function handleApplicationCommand($payload)
    {
        // TODO: Implementar lógica para lidar com comandos de aplicação (slash commands)
        Log::info('Comando de aplicação recebido', ['command_data' => Arr::get($payload, 'data.name', 'N/A')]);
        return response()->json(['status' => 'ok', 'message' => 'Comando de aplicação recebido, mas ainda não processado.']);
    }

    /**
     * Lida com componentes de mensagem recebidos via webhook.
     * Método placeholder para implementar a lógica de processamento de interações com componentes de mensagem (botões, menus) enviados pelo Discord.
     * **IMPLEMENTAÇÃO PENDENTE: Lógica para lidar com componentes de mensagem.**
     * Necessita implementação da lógica para cada tipo de componente e interação suportada.
     * @param array $payload Payload do webhook contendo os dados do componente de mensagem.
     */
    protected function handleMessageComponent($payload)
    {
        // TODO: Implementar lógica para lidar com componentes de mensagem (botões, menus)
        Log::info('Componente de mensagem recebido', ['component_type' => Arr::get($payload, 'data.component_type', 'N/A'), 'custom_id' => Arr::get($payload, 'data.custom_id', 'N/A')]);
        return response()->json(['status' => 'ok', 'message' => 'Componente de mensagem recebido, mas ainda não processado.']);
    }
}