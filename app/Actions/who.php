<?php
class who implements Action {
	public static function ok(Client $client) {
		return true;
	} // function ok

	public static function run(Client $client, $input) {
		//$users = array();
		foreach (Server::getClients() as $c) {
			//$users[] = $c->user;
			$client->message('[some info...] ' . $c->user->name);
		}

	} // function run
}
