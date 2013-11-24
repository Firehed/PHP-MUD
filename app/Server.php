<?php

class Server implements SocketServerDelegate {

	private $address;
	private $port;

	private $socketServer;

	private $clients = array(); // Connected clients
	private $run     = TRUE;    // Run the socket loop while true

	public function clientConnected(SocketClient $sc) {
		Log::info("New client connected: ".$sc->getId());
		$c = new Client($sc, $this);
		$this->clients[$sc->getId()] = $c;
		$c->message('{bWelcome to the club!');
		$c->message('Username: ');
	}

	public function clientDisconnected(SocketClient $sc) {
		Log::info("Client disconnected: ".$sc->getId());
		unset($this->clients[$sc->getId()]);
	}

	public function clientSentMessage(SocketClient $sc, $message) {
		Log::info("Client changed state: ".$sc->getId());
		$client = $this->clients[$sc->getId()];
		// handleInput may force a disconnect
		try {
			$client->handleInput($message);
		}
		catch (DisconnectClientException $e) {
			$client->message($e);
			$client->disconnect();
		}
	}

	public function getClients() {
		return $this->clients;
	} // function getClients

	public function messageAll($message) {
		foreach ($this->clients as $client) {
			$client->message($message);
		}
	} // function messageAll

	private function run() {
		Actions::register();
		while ($this->run) {
			$this->socketServer->handleReads();
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
		if ($this->socketServer !== NULL) {
			throw new Exception('One server at a time!');
		}

		Log::info("Binding to port $this->port at $this->address.");
		$socketServer = new SocketServer($this);
		$socketServer->start($this->address, $this->port);
		$this->socketServer = $socketServer;

		$this->run();
	} // function start

	public function stop() {
		$this->messageAll('{g*** The server is shutting down now. ***');
		foreach ($this->clients as $client) {
			$client->disconnect();
		}
		$this->socketServer->close();
		$this->run = FALSE;
		Database::instance()->close();
	} // function stop

} // class Server
