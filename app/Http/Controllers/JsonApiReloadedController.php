<?php

namespace App\Http\Controllers;

use App\Services\JSONAPI;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class JsonApiReloadedController extends Controller
{
    protected JSONAPI $jsonApiService;

    public function __construct(JSONAPI $jsonApiService)
    {
        $this->jsonApiService = $jsonApiService;
    }

    /**
     * Envia uma mensagem de broadcast com um nome específico
     *
     * @param Request $request Contendo a mensagem e o nome a ser usado
     * @return JsonResponse Resposta da API indicando sucesso ou falha
     */
    public function broadcastWithName(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('broadcastWithName', [
            $request->input('message'),
            $request->input('name')
        ]);
    }

    /**
     * Envia uma mensagem privada para um jogador específico
     *
     * @param Request $request Contendo o nome do jogador e a mensagem
     * @return JsonResponse
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $response = $this->jsonApiResponse('sendMessage', [
            $request->input('playerName'),
            $request->input('message')
        ], true);

        /** @var array|null $responseData */
        $responseData = json_decode($response->getContent(), true);

        if (!is_array($responseData)) {
            Log::error("Invalid API response format", ['response' => $response->getContent()]);
            return response()->json(['success' => false, 'error' => 'Invalid API response format'], 500);
        }

        $firstItem = $responseData[0] ?? null;

        if (!$firstItem || !isset($firstItem['is_success']) || $firstItem['is_success'] !== true) {
            Log::error("Failed to send message", [
                'player' => $request->input('playerName'),
                'api_response' => $responseData
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to send message: ' . ($firstItem['result'] ?? 'Unknown error from API')
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $responseData
        ]);
    }

    /**
     * Envia uma mensagem para todos os jogadores no servidor
     *
     * @param Request $request Contendo a mensagem a ser enviada
     * @return JsonResponse Resposta da API com o número de jogadores que receberam a mensagem
     */
    public function broadcast(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('broadcast', [
            $request->input('message')
        ], true);
    }


    /**
     * Adiciona conteúdo ao final de um arquivo
     *
     * @param Request $request Contendo o caminho do arquivo e o conteúdo a ser adicionado
     * @return JsonResponse Resposta da API
     */
    public function fsAppend(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('fs.append', [
            $request->input('filePath'),
            $request->input('content')
        ]);
    }

    /**
     * Cria um novo arquivo
     *
     * @param Request $request Contendo o caminho do arquivo a ser criado
     * @return JsonResponse Resposta da API
     */
    public function fsCreateFile(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('fs.createFile', [
            $request->input('filePath')
        ]);
    }

    /**
     * Cria uma nova pasta
     *
     * @param Request $request Contendo o caminho da pasta a ser criada
     * @return JsonResponse Resposta da API
     */
    public function fsCreateFolder(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('fs.createFolder', [
            $request->input('folderPath')
        ]);
    }

    /**
     * Exclui um arquivo ou pasta
     *
     * @param Request $request Contendo o caminho do arquivo/pasta a ser excluído
     * @return JsonResponse Resposta da API
     */
    public function fsDelete(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('fs.delete', [
            $request->input('path')
        ]);
    }

    /**
     * Lista o conteúdo de um diretório
     *
     * @param Request $request Contendo o caminho do diretório
     * @return JsonResponse Resposta da API com a lista de arquivos/pastas
     */
    public function fsListDirectory(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('fs.listDirectory', [
            $request->input('directoryPath')
        ]);
    }

    /**
     * Move ou renomeia um arquivo ou pasta
     *
     * @param Request $request Contendo o caminho antigo e o novo caminho
     * @return JsonResponse Resposta da API
     */
    public function fsMove(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('fs.move', [
            $request->input('oldPath'),
            $request->input('newPath')
        ]);
    }

    /**
     * Lê o conteúdo de um arquivo de texto
     *
     * @param Request $request Contendo o caminho do arquivo
     * @return JsonResponse Resposta da API com o conteúdo do arquivo
     */
    public function fsRead(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('fs.read', [
            $request->input('filePath')
        ]);
    }

    /**
     * Lê o conteúdo de um arquivo binário
     *
     * @param Request $request Contendo o caminho do arquivo
     * @return JsonResponse Resposta da API com o conteúdo binário
     */
    public function fsReadBinary(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('fs.readBinary', [
            $request->input('filePath')
        ]);
    }

    /**
     * Escreve conteúdo em um arquivo de texto
     *
     * @param Request $request Contendo o caminho do arquivo e o conteúdo a ser escrito
     * @return JsonResponse Resposta da API
     */
    public function fsWrite(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('fs.write', [
            $request->input('filePath'),
            $request->input('content')
        ]);
    }

    /**
     * Escreve conteúdo binário em um arquivo
     *
     * @param Request $request Contendo o caminho do arquivo e o conteúdo em base64
     * @return JsonResponse Resposta da API
     */
    public function fsWriteBinary(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('fs.writeBinary', [
            $request->input('filePath'),
            $request->input('base64Content')
        ]);
    }

    /**
     * Obtém as últimas mensagens do chat
     *
     * @param Request $request Contendo o limite de mensagens a serem retornadas
     * @return JsonResponse Resposta da API com as mensagens
     */
    public function getLatestChatsWithLimit(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 30);
        return $this->jsonApiResponse('getLatestChatsWithLimit', [$limit]);
    }

    /**
     * Executa um comando no console
     *
     * @param Request $request Contendo o comando a ser executado
     * @return JsonResponse Resposta da API
     */
    public function runCommand(Request $request): JsonResponse
    {
        $command = $request->input('command');

        if (empty($command)) {
            return response()->json([
                'result' => 'error',
                'is_success' => false,
                'error' => [
                    'code' => 400,
                    'message' => 'Command parameter is required'
                ]
            ], 400);
        }

        return $this->jsonApiResponse('runConsoleCommand', [$command]);
    }

    /**
     * Obtém o uso de memória do Java
     *
     * @return array Resposta da API com informações de uso de memória
     */
    public function getJavaMemoryUsage(): array
    {
        return $this->handleApiResponse($this->jsonApiService->getJavaMemoryUsage());
    }

    /**
     * Processa a resposta da API
     *
     * @param mixed $apiResponse Resposta da API
     * @return array Resposta formatada
     */
    private function handleApiResponse($apiResponse): array
    {
        if (isset($apiResponse['error'])) {
            return [
                'result' => 'error',
                'source' => $apiResponse['source'],
                'is_success' => false,
                'error' => [
                    'code' => $apiResponse['error']['code'],
                    'message' => $apiResponse['error']['message']
                ]
            ];
        } else {
            return [
                'result' => 'success',
                'success' => $apiResponse,
                'source' => $apiResponse,
                'is_success' => true
            ];
        }
    }

    /**
     * Teleporta um jogador para uma localização específica
     *
     * @param Request $request Contendo nome do jogador e coordenadas
     * @return JsonResponse Resposta da API
     */
    public function teleportPlayer(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('teleportPlayerToLocation', [
            'playerName' => $request->input('playerName'),
            'x' => $request->input('x'),
            'y' => $request->input('y'),
            'z' => $request->input('z')
        ]);
    }

    /**
     * Dá um item para um jogador
     *
     * @param Request $request Contendo nome do jogador, nome do item e quantidade
     * @return JsonResponse Resposta da API
     */
    public function givePlayerItem(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('takePlayerInventory', [
            'playerName' => $request->input('playerName'),
            'itemName' => $request->input('itemName'),
            'quantity' => $request->input('quantity')
        ]);
    }

    /**
     * Define o tempo de um mundo
     *
     * @param Request $request Contendo nome do mundo e tempo
     * @return JsonResponse Resposta da API
     */
    public function setWorldTime(Request $request): JsonResponse
    {
        return $this->jsonApiResponse('setWorldTime', [
            'worldName' => $request->input('worldName'),
            'time' => $request->input('time')
        ]);
    }

    /**
     * Obtém estatísticas do servidor
     *
     * @return JsonResponse Resposta da API com estatísticas
     */
    public function getServerStats(): JsonResponse
    {
        return $this->jsonApiResponse('getServerStats');
    }

    /**
     * Obtém a versão do servidor
     *
     * @return JsonResponse Resposta da API com a versão
     */
    public function getServerVersion(): JsonResponse
    {
        return $this->jsonApiResponse('getServerVersion');
    }

    /**
     * Bane um jogador
     *
     * @param Request $request Contendo nome do jogador
     * @return JsonResponse Resposta da API
     */
    public function banPlayer(Request $request): JsonResponse
    {
        $playerName = $request->input('playerName');
        return $this->jsonApiResponse('banPlayer', ['playerName' => $playerName]);
    }

    /**
     * Desbane um jogador
     *
     * @param Request $request Contendo nome do jogador
     * @return JsonResponse Resposta da API
     */
    public function unbanPlayer(Request $request): JsonResponse
    {
        $playerName = $request->input('playerName');
        return $this->jsonApiResponse('unbanPlayer', ['playerName' => $playerName]);
    }

    /**
     * Obtém detalhes dos jogadores online
     *
     * @return JsonResponse Resposta da API com detalhes dos jogadores
     */
    public function getOnlinePlayersDetails(): JsonResponse
    {
        return $this->jsonApiResponse('getOnlinePlayersDetails');
    }

    /**
     * Obtém a quantidade de jogadores online
     *
     * @return JsonResponse Resposta da API com a contagem de jogadores
     */
    public function getPlayerCount(): JsonResponse
    {
        return $this->jsonApiResponse('getPlayerCount');
    }

    /**
     * Obtém os nomes dos jogadores online em um mundo específico
     *
     * @param Request $request Contendo o nome do mundo
     * @return JsonResponse Resposta da API com os nomes dos jogadores
     */
    public function getOnlinePlayerNamesInWorld(Request $request): JsonResponse
    {
        $worldName = $request->input('worldName');
        return $this->jsonApiResponse('getOnlinePlayerNamesInWorld', ['worldName' => $worldName]);
    }

    /**
     * Obtém o inventário de um jogador (online ou offline)
     *
     * @param Request $request Contendo o nome do jogador
     * @return array Objeto de inventário representando o inventário do jogador
     */
    public function getPlayerInventory(Request $request): JsonResponse
    {
        $playerName = $request->input('playerName');
        return $this->jsonApiResponse('getPlayerInventory', ['playerName' => $playerName]);
    }

    /**
     * Obtém um slot específico do inventário de um jogador (online ou offline)
     *
     * @param Request $request Contendo o nome do jogador e número do slot
     * @return JsonResponse Objeto ItemStack representando o item no slot especificado
     */
    public function getPlayerInventorySlot(Request $request): JsonResponse
    {
        $playerName = $request->input('playerName');
        $slot = $request->input('slot');
        return $this->jsonApiResponse('getPlayerInventorySlot', [
            'playerName' => $playerName,
            'slot' => $slot
        ], true);
    }

    /**
     * Verifica a conexão com o servidor
     *
     * @return JsonResponse Resposta da API indicando se o servidor está conectado
     */
    public function checkServerConnection(): JsonResponse
    {
        $connectionStatus = $this->jsonApiService->checkConnection();
        return response()->json(['is_connected' => $connectionStatus], $connectionStatus ? 200 : 503);
    }

    /**
     * Processa e retorna a resposta da API JSON
     *
     * @param string $method Método da API a ser chamado
     * @param array $parameters Parâmetros para o método
     * @param bool $checkEmpty Se deve verificar parâmetros vazios
     * @return JsonResponse
     */
    private function jsonApiResponse(string $method, array $parameters = [], bool $checkEmpty = false): JsonResponse
    {
        if ($checkEmpty && in_array(null, $parameters, true)) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        try {
            $response = $this->jsonApiService->call($method, $parameters);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'result' => 'error',
                'source' => $method,
                'is_success' => false,
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }
}
