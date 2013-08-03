<?php

/**
 * Copyright (c) 2012 by Aleksandar Palic, @_skripted on Twitter
 * Released under MIT license
 *
 * This is the main interface for the websocket server and the user's application. Both have to implement
 * it so that the websocket server can fire them after finishing the socket manipulation. This way the
 * user doesn't have to struggle with the server at all and can put his code right into the event handlers.
 */

interface HandlerInterface {
    function onOpen($conn);
    function onClose($conn);
    function onError($error);
    function onMessage($conn, $data);
}