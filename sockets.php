#!/usr/bin/php
<?php
set_time_limit(0);
include 'config.php';
include 'app/Action.php';
include 'app/Client.php';
include 'app/Database.php';
include 'app/Server.php';
include 'app/User.php';

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
