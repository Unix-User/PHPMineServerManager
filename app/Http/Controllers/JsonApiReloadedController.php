<?php

namespace App\Http\Controllers;

use App\Services\JSONAPI;
use Illuminate\Http\Request;

class JsonApiReloadedController extends Controller
{
    protected $jsonApiService;

    public function __construct(JSONAPI $jsonApiService)
    {
        $this->jsonApiService = $jsonApiService;
    }

    /**
     * Adiciona conteúdo ao final de um arquivo
     *
     * @param Request $request Contendo o caminho do arquivo e o conteúdo a ser adicionado
     * @return array Resposta da API
     */
    public function fsAppend(Request $request)
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
     * @return array Resposta da API
     */
    public function fsCreateFile(Request $request)
    {
        return $this->jsonApiResponse('fs.createFile', [
            $request->input('filePath')
        ]);
    }

    /**
     * Cria uma nova pasta
     *
     * @param Request $request Contendo o caminho da pasta a ser criada
     * @return array Resposta da API
     */
    public function fsCreateFolder(Request $request)
    {
        return $this->jsonApiResponse('fs.createFolder', [
            $request->input('folderPath')
        ]);
    }

    /**
     * Exclui um arquivo ou pasta
     *
     * @param Request $request Contendo o caminho do arquivo/pasta a ser excluído
     * @return array Resposta da API
     */
    public function fsDelete(Request $request)
    {
        return $this->jsonApiResponse('fs.delete', [
            $request->input('path')
        ]);
    }

    /**
     * Lista o conteúdo de um diretório
     *
     * @param Request $request Contendo o caminho do diretório
     * @return array Resposta da API com a lista de arquivos/pastas
     */
    public function fsListDirectory(Request $request)
    {
        return $this->jsonApiResponse('fs.listDirectory', [
            $request->input('directoryPath')
        ]);
    }

    /**
     * Move ou renomeia um arquivo ou pasta
     *
     * @param Request $request Contendo o caminho antigo e o novo caminho
     * @return array Resposta da API
     */
    public function fsMove(Request $request)
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
     * @return array Resposta da API com o conteúdo do arquivo
     */
    public function fsRead(Request $request)
    {
        return $this->jsonApiResponse('fs.read', [
            $request->input('filePath')
        ]);
    }

    /**
     * Lê o conteúdo de um arquivo binário
     *
     * @param Request $request Contendo o caminho do arquivo
     * @return array Resposta da API com o conteúdo binário
     */
    public function fsReadBinary(Request $request)
    {
        return $this->jsonApiResponse('fs.readBinary', [
            $request->input('filePath')
        ]);
    }

    /**
     * Escreve conteúdo em um arquivo de texto
     *
     * @param Request $request Contendo o caminho do arquivo e o conteúdo a ser escrito
     * @return array Resposta da API
     */
    public function fsWrite(Request $request)
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
     * @return array Resposta da API
     */
    public function fsWriteBinary(Request $request)
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
     * @return array Resposta da API com as mensagens
     */
    public function getLatestChatsWithLimit(Request $request)
    {
        return $this->jsonApiResponse('getLatestChatsWithLimit', ['limit' => $request->input('limit', 10)]);
    }

    /**
     * Executa um comando no console
     *
     * @param Request $request Contendo o comando a ser executado
     * @return array Resposta da API
     */
    public function runCommand(Request $request)
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
        
        // The JSONAPI expects exactly one argument for runConsoleCommand
        return $this->jsonApiResponse('runConsoleCommand', [$command]);
    }

    /**
     * Obtém o uso de memória do Java
     *
     * @return array Resposta da API com informações de uso de memória
     */
    public function getJavaMemoryUsage()
    {
        return $this->handleApiResponse($this->jsonApiService->getJavaMemoryUsage());
    }

    /**
     * Processa a resposta da API
     *
     * @param array $apiResponse Resposta da API
     * @return array Resposta formatada
     */
    private function handleApiResponse($apiResponse)
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
     * @return array Resposta da API
     */
    public function teleportPlayer(Request $request)
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
     * @return array Resposta da API
     */
    public function givePlayerItem(Request $request)
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
     * @return array Resposta da API
     */
    public function setWorldTime(Request $request)
    {
        return $this->jsonApiResponse('setWorldTime', [
            'worldName' => $request->input('worldName'), 
            'time' => $request->input('time')
        ]);
    }

    /**
     * Obtém estatísticas do servidor
     *
     * @return array Resposta da API com estatísticas
     */
    public function getServerStats()
    {
        return $this->jsonApiResponse('getServerStats');
    }

    /**
     * Obtém a versão do servidor
     *
     * @return array Resposta da API com a versão
     */
    public function getServerVersion()
    {
        return $this->jsonApiResponse('getServerVersion');
    }

    /**
     * Bane um jogador
     *
     * @param Request $request Contendo nome do jogador
     * @return array Resposta da API
     */
    public function banPlayer(Request $request)
    {
        $playerName = $request->input('playerName');
        return $this->jsonApiResponse('banPlayer', ['playerName' => $playerName]);
    }

    /**
     * Desbane um jogador
     *
     * @param Request $request Contendo nome do jogador
     * @return array Resposta da API
     */
    public function unbanPlayer(Request $request)
    {
        $playerName = $request->input('playerName');
        return $this->jsonApiResponse('unbanPlayer', ['playerName' => $playerName]);
    }

    /**
     * Obtém detalhes dos jogadores online
     *
     * @return array Resposta da API com detalhes dos jogadores
     */
    public function getOnlinePlayersDetails()
    {
        return $this->jsonApiResponse('getOnlinePlayersDetails');
    }

    /**
     * Obtém a quantidade de jogadores online
     *
     * @return array Resposta da API com a contagem de jogadores
     */
    public function getPlayerCount()
    {
        return $this->jsonApiResponse('getPlayerCount');
    }

    /**
     * Obtém os nomes dos jogadores online em um mundo específico
     *
     * @param Request $request Contendo o nome do mundo
     * @return array Resposta da API com os nomes dos jogadores
     */
    public function getOnlinePlayerNamesInWorld(Request $request)
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
    public function getPlayerInventory(Request $request)
    {
        $playerName = $request->input('playerName');
        return $this->jsonApiResponse('getPlayerInventory', ['playerName' => $playerName]);
    }

    /**
     * Obtém um slot específico do inventário de um jogador (online ou offline)
     *
     * @param Request $request Contendo o nome do jogador e número do slot
     * @return array Objeto ItemStack representando o item no slot especificado
     */
    public function getPlayerInventorySlot(Request $request)
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
     * @return array Resposta da API indicando se o servidor está conectado
     */
    public function checkServerConnection()
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
     * @return array Resposta formatada da API
     */
    private function jsonApiResponse($method, $parameters = [], $checkEmpty = false)
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
