<?php

class Server {

	private static $app;
	private static $run = TRUE;
	private static $clients = array();
	
	public static function start($address, $port) {
		if (self::$app !== NULL) throw new Exception('One server at a time!');
		
		self::$app = socket_create(AF_INET, SOCK_STREAM, 0);
		socket_set_option(self::$app, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_set_nonblock(self::$app);
		if (!socket_bind(self::$app, $address, $port)) throw new Exception('Cant bind');
		socket_listen(self::$app);
		
		self::run();
	} // function start
	
	public static function stop() {
		self::messageAll("\033[32m*** The server is shutting down now. *** \033[0m");
		foreach (self::$clients as $client) {
			$client->disconnect();
		}
		socket_close(self::$app);
		self::$run = FALSE;
		Database::instance()->close();
	} // function stop
	
	/**
	 * The main program loop
	**/
	private static function run() {
		$w = $e = null;
		while (self::$run) {
			$sockets = self::getSockets();
			if (socket_select($sockets, $w, $e, NULL) > 0) {
				foreach ($sockets as $position => $socket) {
					if ($socket == self::$app) {
						self::addClient($socket);
					}
					else {
						$client = self::$clients[$position];
						try {
							$client->handleInput();
						}
						catch (ClientDisconnectException $e) {
							$client->message($e);
							$client->disconnect();
						}
					}
				}
			}
			unset($sockets);
		}
	} // function run
	
	public static function messageAll($message) {
		foreach (self::$clients as $client) {
			$client->message($message);
		}
	} // function messageAll
	
	private static function getSockets() {
		$sockets[0] = self::$app;
		foreach (self::$clients as $client) {
			$sockets[$client->getPosition()] = $client->getSocket();
		}
		return $sockets;
	} // function getSockets
	
	private static function addClient($socket) {
		if ($new = socket_accept(self::$app)) {
			$c = new Client($new);
			self::$clients[$c->getPosition()] = $c;
		}
	} // function addClient
	
	public static function removeClient(Client $c) {
		unset(self::$clients[$c->getPosition()]);
	} // function removeClient
	
} // class Server
