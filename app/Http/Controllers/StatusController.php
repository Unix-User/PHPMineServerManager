<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatusController extends Controller
{
    private $host;
    private $port = 25565;

    public function __construct()
    {
        // $this->host = gethostbyname(env('MINECRAFT_SERVER'));
    }

    public function show()
    {
        $minecraftRconController = new MinecraftRconController();
        
        // Execute 'list' command to get player information
        $response = $minecraftRconController->executeInternalCommand(new Request(['command' => 'list']));
        $responseContent = $this->cleanResponseContent($response->getContent());
        $responseArray = json_decode($responseContent, true);
        $responseContent = $this->removeFormattingCodes($responseArray['response']);
        
        // Extract player count and list
        $playerData = $this->extractPlayerData($responseContent);
        
        // Execute '/version' command to get server version
        $versionResponse = $minecraftRconController->executeInternalCommand(new Request(['command' => 'version']));
        $versionResponseContent = $this->cleanResponseContent($versionResponse->getContent());
        $versionResponseArray = json_decode($versionResponseContent, true);
        $serverVersion = $this->extractServerVersion($versionResponseArray['response']);
        
        return response()->json([
            'response' => $responseArray,
            'responseclean' => $responseContent,
            'javaVersion' => $this->getJavaVersion(),
            'isProgramRunning' => $this->isServiceRunning(),
            'jogadores' => $playerData['count'],
            'maxJogadores' => $playerData['max'],
            'online' => $playerData['players'],
            'serverVersion' => $serverVersion
        ]);
    }

    private function cleanResponseContent($content)
    {
        return str_replace('\u0000\u0000', '', $content);
    }

    private function removeFormattingCodes($text)
    {
        return preg_replace('/§./', '', $text);
    }

    private function extractPlayerData($response)
    {
        preg_match('/Há (\d+) de no máximo (\d+) jogadores online./', $response, $matches);
        $count = $matches[1] ?? '0';
        $max = $matches[2] ?? '20';
        $players = explode("\n", trim(str_replace($matches[0], '', $response)));
        
        return [
            'count' => $count,
            'max' => $max,
            'players' => $players
        ];
    }

    private function extractServerVersion($response)
    {
        preg_match('/MC: (\d+\.\d+\.\d+)/', $response, $matches);
        return $matches[1] ?? 'Unknown';
    }

    private function getJavaVersion()
    {
        exec('java -version 2>&1', $output);
        preg_match('/version "(.*?)"/', $output[0], $matches);
        return $matches[1] ?? 'Unknown';
    }
    
    private function isServiceRunning()
    {
        $serviceName = 'Minecraft_Server';
        $serviceStatus = shell_exec('sc query ' . $serviceName);

        if (strpos($serviceStatus, '1060') !== false) {
            return ['installed' => false, 'running' => false];
        } else {
            return ['installed' => true, 'running' => strpos($serviceStatus, 'RUNNING') !== false];
        }
    }

    private function getPortStatus()
    {
        $connection = @fsockopen($this->host, $this->port);

        if (is_resource($connection)) {
            fclose($connection);
            return 'Aberta';
        } else {
            error_log('Unable to establish connection');
            return 'Fechada';
        }
    }

    public function overviewerIsRunning()
    {
        $processName = 'overviewer.exe';
        $command = 'tasklist /FI "IMAGENAME eq ' . $processName . '"';
        $processes = shell_exec($command);
        $processes = mb_convert_encoding($processes, 'UTF-8', 'UTF-8');
        $isRunning = strpos($processes, $processName) !== false;
        return ['isBusy' => $isRunning, 'output' => $processes];
    }
}
?>