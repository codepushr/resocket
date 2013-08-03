<?php

/**
 * Copyright (c) 2012 by Aleksandar Palic, @_skripted on Twitter
 * Released under MIT license
 *
 * This is a custom application protocol which handles the client-side events that come with the messages.
 * It extends the Protocol class which evaluates and splites the message to get you the event name and the
 * raw message.
 */

require_once('Resocket/Protocol.php');

class ApplicationProtocol extends Protocol {

    public function handleEvent($conn, $data) {
        $array = $this->splitMessage($data);

        /**
         * In this case the client sent a message with a special event on the beginning. You can now easily
         * react to that and go on with your application logics.
         */
        switch ($array[0]) {
            case 'event_1':
                $conn->send($array[1] . ' returned!');
                break;

            default:
                throw new Exception('Error: No or too many delimiters found.');
        }
    }
}