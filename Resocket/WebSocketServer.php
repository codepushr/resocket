<?php

/**
 * Copyright (c) 2012 by Aleksandar Palic, @_skripted on Twitter
 * Released under MIT license
 *
 * This is the main class for the websocket server. It extends the Encoder class which encodes and decodes
 * messages defined in the websocket protocol. It also implements the HandlerInterface which suggests that
 * it must implement the needed event methods onOpen, onClode, onMessage and onError.
 */

require_once('HandlerInterface.php');
require_once('Connection.php');
require_once('Encoder.php');

class WebSocketServer extends Encoder implements HandlerInterface {
    private $serverSocket = null;
    private $socketCollection = array();
    private $connectionCollection = array();
    private $application;

    public function __construct(HandlerInterface $application, $address, $port) {
        error_reporting(E_ALL);     // set error reporting
        set_time_limit(0);          // set unlimited script execution
        ob_implicit_flush(true);    // set automatic flush

        $this->prepareServerSocket($address, $port);
        $this->application = $application;
    }

    /**
     * This is the main loop for the websocket server. It listenes on changes in it's local socket array
     * and adds new connections. It also triggers the onOpen method and if the handshake is complete the
     * onMessage method.
     */
    public function run() {
        while (true) {
            $sockets = $this->socketCollection;
            socket_select($sockets, $write = NULL, $except = NULL, NULL);

            foreach($sockets as $socket) {
                if($socket == $this->serverSocket){
                    $client = socket_accept($this->serverSocket);

                    if($client < 0) { $this->onError(4); continue; }
                    else { $this->onOpen($client); }
                }
                else {
                    $bytes = @socket_recv($socket, $buffer, 2048, 0);

                    if($bytes == 0) { $this->onClose($socket); }
                    else {
                        $conn = $this->connectionCollection[$this->getConnectionIndexBySocket($socket)];

                        if(!$conn->handshake) { $this->handShake($conn, $buffer); }
                        else { $this->onMessage($conn, $this->decode($buffer)); }
                    }
                }
            }
        }
    }

    /**
     * Those four functions have to be implemented because of the interface and handle the different states
     * of the websocket connections. As of the special architecture the application also has to implement
     * the HandlerInterface which allows us to fire them after the server finished handling the sockets.
     */
    public function onOpen($socket) {
        $conn = new Connection($socket);
        $this->addToCollections($socket, $conn);
        $this->application->onOpen($conn);
    }

    public function onClose($socket) {
        $this->application->onClose($this->connectionCollection[$this->getConnectionIndexBySocket($socket)]);
        $this->removeFromCollections($socket);
        socket_close($socket);
    }

    public function onError($error) {
        $this->application->onError($error);
        die;
    }

    public function onMessage($from, $data) {
        $this->application->onMessage($from, $data);
    }

    /**
     * The core of the websocket connection establishment is the handShake function. It is implemented as of
     * RFC6455 and the websocket version 13. It filters the request header and generates the accept key.
     */
    public function handShake($conn, $buffer) {
        if(preg_match("/Sec-WebSocket-Version: (.*)\r\n/ ", $buffer, $match)) $version = $match[1];
        else return false;

        if($version == 13) {
            if(preg_match("/GET (.*) HTTP/"   , $buffer, $match)){ $r = $match[1]; }
            if(preg_match("/Host: (.*)\r\n/"  , $buffer, $match)){ $h = $match[1]; }
            if(preg_match("/Sec-WebSocket-Origin: (.*)\r\n/", $buffer, $match)){ $o = $match[1]; }
            if(preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $buffer, $match)){ $k = $match[1]; }

            $accept_key = $k . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
            $accept_key = sha1($accept_key, true);
            $accept_key = base64_encode($accept_key);

            $upgrade =  "HTTP/1.1 101 Switching Protocols\r\n" .
                        "Upgrade: websocket\r\n" .
                        "Connection: Upgrade\r\n" .
                        "Sec-WebSocket-Accept: " . $accept_key . "\r\n\r\n";

            socket_write($conn->socket, $upgrade, strlen($upgrade));
            $conn->handshake = true;

            return true;
        }
    }

    public function prepareServerSocket($address, $port) {
        $this->serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or $this->onError(0);
        socket_set_option($this->serverSocket, SOL_SOCKET, SO_REUSEADDR, 1) or $this->onError(1);
        socket_bind($this->serverSocket, $address, $port) or $this->onError(2);
        socket_listen($this->serverSocket, 20) or $this->onError(3);
        array_push($this->socketCollection, $this->serverSocket);
    }

    public function getConnectionIndexBySocket($socket) {
        for ($i = 0; $i < count($this->connectionCollection); $i ++) {
            if ($this->connectionCollection[$i]->socket == $socket) {
                return $i;
            }
        }

        return false;
    }

    public function addToCollections($socket, $conn) {
        array_push($this->socketCollection, $socket);
        array_push($this->connectionCollection, $conn);
    }

    public function removeFromCollections($socket) {
        $index = $this->getConnectionIndexBySocket($socket);
        if (is_numeric($index)) array_splice($this->connectionCollection, $index, 1);

        $index = array_search($socket, $this->socketCollection);
        if ($index >= 0) array_splice($this->socketCollection, $index, 1);
    }
}