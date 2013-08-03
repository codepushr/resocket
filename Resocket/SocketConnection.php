<?php

class SocketConnection {
    public $connectionId;
    public $socket;
    public $handshake;
    public $date;

    public function __construct($socket) {
        $this->connectionId = uniqid();
        $this->socket = $socket;
        $this->handshake = false;
        $this->date = date("d/n/Y H:i:s T");
    }
}