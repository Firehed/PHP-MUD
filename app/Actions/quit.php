<?php

class quit implements Action {
	public static function ok(Client $client) {
		return true;
	}

	public static function run(Client $client, $arg) {
		$client->quit();
	}
}
