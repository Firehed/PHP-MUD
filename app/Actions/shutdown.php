<?php

class shutdow implements Action {
	public static function ok(Client $client) {
		return true;
	} // function ok
	
	public static function run(Client $client, $arg) {
		$client->message('You need to spell out the entire SHUTDOWN command to shut down the server.');
	} // function run
}

class shutdown implements Action {
	public static function ok(Client $client) {
		return true;
	}

	public static function run(Client $client, $arg) {
		Server::stop();
	}
}
