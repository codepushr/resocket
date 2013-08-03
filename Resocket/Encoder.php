<?php

/**
 * Copyright (c) 2012 by Aleksandar Palic, @_skripted on Twitter
 * Released under MIT license
 *
 * This is the base class of WebSocketServer and Connection. It includes functions to encode and decode messages
 * based on the RFC6455.
 */

class Encoder {

    public function encode($text) {
        // 0x1 text frame (FIN + opcode)
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);
        $header = null;

        if($length <= 125) $header = pack('CC', $b1, $length);
        elseif($length > 125 && $length < 65536) $header = pack('CCS', $b1, 126, $length);
        elseif($length >= 65536) $header = pack('CCN', $b1, 127, $length);

        return $header . $text;
    }

    public function decode($payload) {
        $length = ord($payload[1]) & 127;

        if($length == 126) {
            $masks = substr($payload, 4, 4);
            $data = substr($payload, 8);
        }
        elseif($length == 127) {
            $masks = substr($payload, 10, 4);
            $data = substr($payload, 14);
        }
        else {
            $masks = substr($payload, 2, 4);
            $data = substr($payload, 6);
        }

        $text = '';
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i%4];
        }

        return $text;
    }
}