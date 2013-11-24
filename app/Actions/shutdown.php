<?php

class shutdown implements Action {

	public static function ok(Client $client) {
		return true;
	} // function ok

	public static function run(Client $client, $cmd, $arg) {
		if (strtolower($cmd) != 'shutdown') {
			$client->message('You need to spell out the entire SHUTDOWN command to shut down the server.');
			return;
		}
		Log::info("Server shut down by {$client->user->name}");
		$client->getServer()->stop();
	} // function run

} // class shutdown
