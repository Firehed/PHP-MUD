<?php
class who implements Action {
	public static function ok(Client $client) {
		return true;
	} // function ok

	public static function run(Client $client, $input) {
		foreach (Server::getClients() as $c) {
			$client->message('[some info...] ' . $c->user->name);
		}
	} // function run
}
