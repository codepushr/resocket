<?php

/**
 * Copyright (c) 2012 by Aleksandar Palic, @_skripted on Twitter
 * Released under MIT license
 *
 * This is the base class of your future application protocols. As described in the sample protocol it has
 * a few functions to break down the client message and filter special events. It also holds the default
 * value for the delimiter (which of course can be rewritten in the application protocol later).
 */

class Protocol {
    private $delimiter;

    public function __construct() {
        $this->delimiter = '#';
    }

    public function evaluateMessage($message) {
        $pos = strpos($message, $this->delimiter);
        if ($pos === false) return false;
        else return true;
    }

    public function splitMessage($message) {
        if ($this->evaluateMessage($message)) {
            $parts = explode($this->delimiter, $message);
            if (count($parts) != 2) return false;
            else return $parts;
        }
        else return false;
    }
}