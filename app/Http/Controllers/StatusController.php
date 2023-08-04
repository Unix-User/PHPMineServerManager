<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StatusController extends Controller
{
    public function show()
    {
        $process = new Process(['cmd', '/c', 'java', '-version']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $javaVersion = $process->getOutput();

        $process = new Process(['cmd', '/c', 'tasklist', '|', 'findstr', 'java']);
        $process->run();

        $isProgramRunning = $process->isSuccessful();

        $ipAddress = gethostbyname(gethostname());

        $socket = @fsockopen($ipAddress, 25565, $errno, $errstr, 1);
        if ($socket)
        {
            $portStatus = 'open';
            fclose($socket);
        }
        else
        {
            $portStatus = 'closed';
        }

        return response()->json([
            'javaVersion' => $javaVersion,
            'isProgramRunning' => $isProgramRunning,
            'ipAddress' => $ipAddress,
            'portStatus' => $portStatus,
        ]);
    }
}
?>

