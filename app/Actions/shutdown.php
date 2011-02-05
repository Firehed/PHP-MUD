<?php

class shutdow implements Action {

	public static function ok(Client $client) {
		return true;
	} // function ok

	public static function run(Client $client, $arg) {
		$client->message('You need to spell out the entire SHUTDOWN command to shut down the server.');
	} // function run

} // class shutdow

class shutdown implements Action {

	public static function ok(Client $client) {
		return true;
	} // function ok

	public static function run(Client $client, $arg) {
		Log::info("Server shut down by {$client->user->name} from {$client->host}.");
		Server::stop();
	} // function run

} // class shutdown
