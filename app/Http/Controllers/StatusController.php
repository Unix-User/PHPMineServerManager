<?php
namespace App\Http\Controllers;

class StatusController extends Controller
{
    private $host;
    private $port = 25565;

    public function __construct()
    {
        $this->host = gethostbyname(gethostname());
    }

    public function show()
    {
        return response()->json([
            'javaVersion' => $this->getJavaVersion(),
            'isProgramRunning' => $this->isServiceRunning(),
            'ipAddress' => $this->host,
            'portStatus' => $this->getPortStatus(),
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
}
?>

