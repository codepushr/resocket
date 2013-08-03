<?php

/**
 * Copyright (c) 2012 by Aleksandar Palic, @_skripted on Twitter
 * Released under MIT license
 *
 * This is the final server script which gets executed by the PHP binary from the console. First you create
 * the instance of your application and then pass it the WebSocketServer instance (along with host and port).
 * Finally fire the run method of the server to initiate the core loop.
 */

require_once('Application/Application.php');
require_once('Resocket/WebSocketServer.php');

$app = new Application();
$server = new WebSocketServer($app, "localhost", 8080);
$server->run();