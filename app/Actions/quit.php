<?php

class quit implements Action {

	public static function ok(Client $client) {
		return true;
	} // function ok

	public static function run(Client $client, $cmd, $arg) {
		$client->quit();
	} // function run

} // class quit
