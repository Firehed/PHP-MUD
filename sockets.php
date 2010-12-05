#!/usr/bin/php
<?php
set_time_limit(0);
error_reporting(-1);

include './config.php';
set_error_handler(function($no,$str,$f,$l) {
	// Handle socket exceptions differently
	if (strstr($str, 'socket_'))
		throw new SocketException;

	if (DEBUG)
		print_r(func_get_args());
	throw new ErrorException($str,0,$no,$f,$l);
}, -1);

include './app/Action.php';
include './app/Client.php';
include './app/Database.php';
include './app/Server.php';
include './app/User.php';

Server::start($address, $port);

/*
30 Black
31 Red
32 Green
33 Yellow
34 Blue
35 Magenta
36 Cyan
37 White
*/

class ClientDisconnectException extends Exception {
	public function __toString() {
		return $this->getMessage();
	} // function __toString
}
class SocketException extends Exception {}
