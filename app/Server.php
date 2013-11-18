<?php

class Server {

	private $address;
	private $port;
	private $socket; // Bound socket

	private $allSockets = [];

	private $clients = array(); // Connected clients
	private $run     = TRUE;    // Run the socket loop while true

	private function addClient($socket) {
		if ($new = socket_accept($this->socket)) {
			$c = new Client(new SocketClient($new), $this);
			$this->clients[$c->position] = $c;
			$this->allSockets[$c->position] = $new;
		}
	} // function addClient

	public function getClients() {
		return $this->clients;
	} // function getClients

	/**
	 * @return array of all socket resources: main server in index 0, all
	 * clients in other indexes
	 */
	private function getSockets() {
		return $this->allSockets;
	} // function getSockets

	public function messageAll($message) {
		foreach ($this->clients as $client) {
			$client->message($message);
		}
	} // function messageAll

	public function removeClient(Client $c) {
		unset($this->clients[$c->position]);
		unset($this->allSockets[$c->position]);
	} // function removeClient

	private function run() {
		Actions::register();
		$write = $except = null;
		while ($this->run) {
			$sockets = $this->getSockets();
			if (socket_select($sockets, $write, $except, 0, 100000) > 0) {
				foreach ($sockets as $position => $socket) {
					if ($socket == $this->socket) {
						$this->addClient($socket);
					}
					else {
						$client = $this->clients[$position];
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

	public function setAddress($address) {
		$this->address = $address;
		return $this;
	}

	public function setPort($port) {
		$this->port = $port;
		return $this;
	}

	public function start() {
		if ($this->socket !== NULL)
			throw new Exception('One server at a time!');

		$address = $this->address;
		$port = $this->port;
		Log::info("Binding to port $port at $address.");
		$this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
		socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_set_nonblock($this->socket);

		if (!socket_bind($this->socket, $address, $port))
			throw new Exception("Can't bind to port $port.");

		socket_listen($this->socket);

		$this->allSockets[0] = $this->socket;

		$this->run();
	} // function start

	public function stop() {
		$this->messageAll('{g*** The server is shutting down now. ***');
		foreach ($this->clients as $client) {
			$client->disconnect();
		}
		socket_close($this->socket);
		$this->run = FALSE;
		Database::instance()->close();
	} // function stop

} // class Server
