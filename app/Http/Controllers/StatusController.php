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
        
        // Execute 'list' command
        $response = $minecraftRconController->executeInternalCommand(new Request(['command' => 'list']));
        $responseContent = str_replace('\u0000\u0000', '', $response->getContent());
        $responseArray = json_decode($responseContent, true);
        preg_match('/There are (\d+) of a max of (\d+) players online: (.*)/', $responseArray['response'], $matches);
        $jogadores = $matches[1];
        $maxJogadores = $matches[2];
        $online = empty($matches[3]) ? explode(', ', 'sem jogadores online') : explode(', ', $matches[3]);
        
        // Execute '/version' command
        $versionResponse = $minecraftRconController->executeInternalCommand(new Request(['command' => 'version']));
        $versionResponseContent = str_replace('\u0000\u0000', '', $versionResponse->getContent());
        $versionResponseArray = json_decode($versionResponseContent, true);
        preg_match('/MC: (\d+\.\d+\.\d+)/', $versionResponseArray['response'], $matches);
        $serverVersion = $matches[1];
        
        return response()->json([
            'javaVersion' => $this->getJavaVersion(),
            'isProgramRunning' => $this->isServiceRunning(),
            // 'ipAddress' => $this->host,
            // 'portStatus' => $this->getPortStatus(),
            'jogadores' => $jogadores,
            'maxJogadores' => $maxJogadores,
            'online' => $online,
            'serverVersion' => $serverVersion
        ]);
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