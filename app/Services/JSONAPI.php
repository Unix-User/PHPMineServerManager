<?php

namespace App\Services;

/**
 * An optimized PHP class for accessing Minecraft servers with Bukkit and the JSONAPI plugin, aligned with JSONAPI-RELOADED documentation.
 * 
 * This class enhances server interactions by streamlining key and URL generation, decoding JSON responses more efficiently, and ensuring compatibility with JSONAPI-RELOADED standards.
 * Additionally, it includes methods for managing players, worlds, plugins, permissions, chat, and server control.
 */
class JSONAPI {
    private $host;
    private $port;
    private $username;
    private $password;
    const URL_FORMAT = 'http://%s:%d/api/2/call?json=%s';
    private $timeout;
    private $salt;

    // Constructor and other existing methods remain unchanged...

    /**
     * Bans a player by name.
     */
    public function banPlayer($playerName) {
        return $this->call('ban', [$playerName]);
    }

    /**
     * Unbans a player by name.
     */
    public function unbanPlayer($playerName) {
        return $this->call('unban', [$playerName]);
    }

    /**
     * Sets the world time.
     */
    public function setWorldTime($worldName, $time) {
        return $this->call('world.setWorldTime', [$worldName, $time]);
    }

    /**
     * Enables a plugin by name.
     */
    public function enablePlugin($pluginName) {
        return $this->call('enablePlugin', [$pluginName]);
    }

    /**
     * Disables a plugin by name.
     */
    public function disablePlugin($pluginName) {
        return $this->call('disablePlugin', [$pluginName]);
    }

    /**
     * Adds a permission to a player.
     */
    public function addPlayerPermission($playerName, $permissionNode) {
        return $this->call('permissions.addPermission', [$playerName, $permissionNode, true]);
    }

    /**
     * Removes a permission from a player.
     */
    public function removePlayerPermission($playerName, $permissionNode) {
        return $this->call('permissions.removePermission', [$playerName, $permissionNode]);
    }

    /**
     * Sends a global message to all players.
     */
    public function sendGlobalMessage($message) {
        return $this->call('broadcast', [$message]);
    }

    /**
     * Sets a player's chat prefix.
     */
    public function setPlayerPrefix($worldName, $playerName, $prefix) {
        return $this->call('chat.setPlayerPrefix', [$worldName, $playerName, $prefix]);
    }

    /**
     * Restarts the server.
     */
    public function restartServer() {
        return $this->call('remotetoolkit.restartServer', []);
    }

    /**
     * A generic method to perform API calls.
     */
    private function call($method, $args = []) {
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

    // Other existing methods remain unchanged...
}
