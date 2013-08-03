<?php

/**
 * Copyright (c) 2012 by Aleksandar Palic, @_skripted on Twitter
 * Released under MIT license
 *
 * This is a small wrapper for the actual socket connection. It basically contains the socket but also includes
 * a unique id, as well as the handshake state and the date and time of the connection.
 */

require_once('Encoder.php');

class Connection extends Encoder {
    public $connId;
    public $socket;
    public $handshake;
    public $date;

    public function __construct($socket) {
        $this->connId = uniqid();
        $this->socket = $socket;
        $this->handshake = false;
        $this->date = date("d/n/Y H:i:s T");
    }

    public function send($data) {
        $data = $this->encode($data);
        socket_write($this->socket, $data, strlen($data));
    }

    public function close() {
        socket_close($this->socket);
    }
}