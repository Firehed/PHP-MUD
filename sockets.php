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

include './app/Actions.php';
include './app/Client.php';
include './app/Database.php';
include './app/Server.php';
include './app/User.php';

Server::start($address, $port);

define('COLOR_RESET',      "\033[0m");    // Reset
define('COLOR_DK_BLACK',   "\033[0;30m"); // Black
define('COLOR_DK_RED',     "\033[0;31m"); // Red
define('COLOR_DK_GREEN',   "\033[0;32m"); // Green
define('COLOR_DK_YELLOW',  "\033[0;33m"); // Yellow
define('COLOR_DK_BLUE',    "\033[0;34m"); // Blue
define('COLOR_DK_MAGENTA', "\033[0;35m"); // Magenta
define('COLOR_DK_CYAN',    "\033[0;36m"); // Cyan
define('COLOR_DK_WHITE',   "\033[0;37m"); // White
define('COLOR_LT_BLACK',   "\033[1;30m"); // Light Black
define('COLOR_LT_RED',     "\033[1;31m"); // Light Red
define('COLOR_LT_GREEN',   "\033[1;32m"); // Light Green
define('COLOR_LT_YELLOW',  "\033[1;33m"); // Light Yellow
define('COLOR_LT_BLUE',    "\033[1;34m"); // Light Blue
define('COLOR_LT_MAGENTA', "\033[1;35m"); // Light Magenta
define('COLOR_LT_CYAN',    "\033[1;36m"); // Light Cyan
define('COLOR_LT_WHITE',   "\033[1;37m"); // Light White



class ClientDisconnectException extends Exception {
	public function __toString() {
		return $this->getMessage();
	} // function __toString
}
class SocketException extends Exception {}
