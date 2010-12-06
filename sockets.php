#!/usr/bin/php
<?php
set_time_limit(0);
error_reporting(-1);

include './config.php';
set_error_handler(function($no,$str,$f,$l) {
	if (DEBUG)
		print_r(func_get_args());
	// Handle socket exceptions differently
	if (strstr($str, 'socket_'))
		throw new SocketException;

	throw new ErrorException($str,0,$no,$f,$l);
}, -1);

include './app/Tick.php';
include './app/Colors.php';
include './app/Actions.php';
include './app/Client.php';
include './app/Database.php';
include './app/Server.php';
include './app/User.php';

register_tick_function(array('Tick','tock'));
declare(ticks = 10); // The Loop runs pretty darn often, let's not overload things

Server::start($address, $port);

class DisconnectClientException extends Exception {
	public function __toString() {
		return $this->getMessage();
	} // function __toString
}
class SocketException extends Exception {}
