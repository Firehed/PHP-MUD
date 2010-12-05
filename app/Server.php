<?php

class Server {

	private static $app;               // Application socket
	private static $clients = array(); // Connected clients
	private static $run = TRUE;        // Run the socket loop while true

	private static function addClient($socket) {
		if ($new = socket_accept(self::$app)) {
			$c = new Client($new);
			self::$clients[$c->position] = $c;
		}
	} // function addClient

	public static function getClients() {
		return self::$clients;
	} // function getClients

	private static function getSockets() {
		$sockets[0] = self::$app;
		foreach (self::$clients as $client) {
			$sockets[$client->position] = $client->socket;
		}
		return $sockets;
	} // function getSockets

	public static function messageAll($message) {
		foreach (self::$clients as $client) {
			$client->message($message);
		}
	} // function messageAll

	public static function removeClient(Client $c) {
		unset(self::$clients[$c->position]);
	} // function removeClient

	private static function run() {
		Actions::register();
		$write = $except = null;
		while (self::$run) {
			$sockets = self::getSockets();
			if (socket_select($sockets, $write, $except, NULL) > 0) {
				foreach ($sockets as $position => $socket) {
					if ($socket == self::$app) {
						self::addClient($socket);
					}
					else {
						$client = self::$clients[$position];
						try {
							$client->handleInput();
						}
						catch (DisconnectClientException $e) {
							$client->message($e);
							$client->disconnect();
						}
					}
				}
			}
			unset($sockets);
		}
	} // function run


	public static function start($address, $port) {
		if (self::$app !== NULL)
			throw new Exception('One server at a time!');

		self::$app = socket_create(AF_INET, SOCK_STREAM, 0);
		socket_set_option(self::$app, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_set_nonblock(self::$app);
		if (!socket_bind(self::$app, $address, $port))
			throw new Exception("Can't bind to port $port.");
		socket_listen(self::$app);

		self::run();
	} // function start

	public static function stop() {
		self::messageAll(COLOR_DK_GREEN . "*** The server is shutting down now. ***");
		foreach (self::$clients as $client) {
			$client->disconnect();
		}
		socket_close(self::$app);
		self::$run = FALSE;
		Database::instance()->close();
	} // function stop

} // class Server
