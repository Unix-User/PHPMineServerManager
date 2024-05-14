<?php

namespace App\Services;

/**
 * A PHP class for accessing Minecraft servers that have Bukkit with the JSONAPI plugin installed.
 * 
 * This class handles everything from key creation to URL creation to actually returning the decoded JSON as an associative array.
 * 
 * @author Alec Gorge <alecgorge@gmail.com>
 * @version Alpha 5
 * @link http://github.com/alecgorge/JSONAPI
 * @package JSONAPI
 * @since Alpha 5
 */
class JSONAPI {
    private $host;
    private $port;
    private $username;
    private $password;
    const URL_FORMAT = 'http://%s:%d/api/2/call?json=%s';
    private $timeout;
    private $salt;

    /**
     * Constructor to initialize the JSONAPI with server details from environment variables.
     */
    public function __construct() {
        $this->host = env('MC_SERVER_HOST', 'localhost');
        $this->port = env('MC_SERVER_PORT', 20059);
        $this->username = env('MC_SERVER_USERNAME', 'admin');
        $this->password = env('MC_SERVER_PASSWORD', 'password');
        $this->salt = env('MC_SERVER_SALT', '');
        $this->timeout = 10; // Default timeout can be adjusted or also set from env if needed
    }

    // Constructor and other existing methods remain unchanged...

    /**
     * System information retrieval methods.
     */
    public function getCpuCores() {
        return $this->errorResponse('system.getCpuCores');
    }

    public function getCpuCurrentFreq($includeTurbo) {
        return $this->errorResponse('system.getCpuCurrentFreq');
    }

    public function getCpuFreq($includeTurbo) {
        return $this->errorResponse('system.getCpuFreq');
    }

    public function getCpuModel() {
        return $this->errorResponse('system.getCpuModel');
    }

    public function getCpuThreads() {
        return $this->errorResponse('system.getCpuThreads');
    }

    public function getDiskFreeSpace() {
        return $this->call('system.getDiskFreeSpace', []);
    }

    public function getDiskSize() {
        return $this->call('system.getDiskSize', []);
    }

    public function getDiskUsage() {
        return $this->call('system.getDiskUsage', []);
    }

    public function getHostMaxMemory($includeSwap) {
        return $this->errorResponse('system.getHostMaxMemory');
    }

    public function getHostMemory($includeSwap) {
        return $this->errorResponse('system.getHostMemory');
    }

    public function getJavaMemoryTotal() {
        return $this->call('system.getJavaMemoryTotal', []);
    }

    public function getJavaMemoryUsage() {
        return $this->call('system.getJavaMemoryUsage', []);
    }

    public function getServerClockDebug() {
        return $this->call('system.getServerClockDebug', []);
    }

    /**
     * Retrieves the current player count from the server.
     */
    public function getPlayerCount() {
        return $this->call('getPlayerCount', []);
    }

    /**
     * Retrieves the server version.
     */
    public function getServerVersion() {
        return $this->call('getServerVersion', []);
    }

    /**
     * Retrieves the names of online players in a specific world.
     */
    public function getOnlinePlayerNamesInWorld($worldName) {
        return $this->call('getOnlinePlayerNamesInWorld', ['worldName' => $worldName]);
    }

    /**
     * A generic method to perform API calls.
     */
    public function call($method, $args = []) {
        $json = $this->constructCall($method, $args);
        $url = $this->makeURL($method, $args);
        return $this->curl($url);
    }

    private function constructCall($method, array $args) {
        $json = array();
        $json['name'] = $method;
        $json['arguments'] = $args;
        $json['key'] = $this->createKey($method);
        $json['username'] = $this->username;
        return $json;
    }

    public function createKey($method) {
        if(is_array($method)) {
            $method = json_encode($method);
        }
        return hash('sha256', $this->username . $method . $this->password . $this->salt);
    }

    public function makeURL($method, array $args) {
        return sprintf(self::URL_FORMAT, $this->host, $this->port, rawurlencode(json_encode($this->constructCall($method, $args))));
    }

    private function curl($url) {
        if(extension_loaded('cURL')) {
            $c = curl_init($url);
            curl_setopt($c, CURLOPT_PORT, $this->port);
            curl_setopt($c, CURLOPT_HEADER, false);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($c, CURLOPT_TIMEOUT, $this->timeout);        
            $result = curl_exec($c);
            curl_close($c);
            return json_decode($result, true);
        }else{
            $opts = array('http' =>
                array(
                    'timeout' => $this->timeout
                    )
                );
            $result = file_get_contents($url, false, stream_context_create($opts));
            return json_decode($result, true);
        }
    }

    /**
     * Checks if the server port is open to ensure connection is possible.
     */
    public function checkConnection() {
        $connection = @fsockopen($this->host, $this->port);
        if ($connection) {
            fclose($connection);
            return true;
        }
        return false;
    }

    private function errorResponse($method) {
        return [
            'result' => 'error',
            'source' => $method,
            'is_success' => false,
            'error' => [
                'code' => 7,
                'message' => "The method '$method' does not exist!"
            ]
        ];
    }

    // Other existing methods remain unchanged...
}

