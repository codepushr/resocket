<?php

/**
 * Copyright (c) 2012 by Aleksandar Palic, @_skripted on Twitter
 * Released under MIT license
 *
 * This is a sample application. It implements the HandlerInterface and therefore the needed event handlers.
 * One does not have to bother with the websocket server. If any event on the server occurs it can be handled
 * by using those four functions. The app also uses a sample ApplicationProtocol which handles the client-
 * side events that come with the messages (usually splitted by a delimiter).
 */

require_once('Resocket/HandlerInterface.php');
require_once('ApplicationProtocol.php');

class Application implements HandlerInterface {
    private $protocol;

    public function __construct() {
        $this->protocol = new ApplicationProtocol();
    }

    public function onOpen($conn) {
        echo 'Connection Opened! Connection-Id: ' . $conn->connId . "\n";
    }

    /**
     * If a message comes in we take the data and give it the application protocol. This protocol should
     * determine what the event means and what you should do with it next.
     */
    public function onMessage($conn, $data) {
        echo 'Message from ' . $conn->connId . ': ' . $data . "\n";
        $this->protocol->handleEvent($conn, $data);
    }

    public function onClose($conn) {
        echo 'Connection Closed! Connection-Id: ' . $conn->connId . "\n";
    }

    public function onError($error) {
        echo 'Oh-oh an error occured. Code: ' . $error . "\n";
    }
}