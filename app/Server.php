<?php

class Server implements SocketServerDelegate {

	private $address;
	private $port;

	private $socketServer;

	private $clientMap = []; // Client -> SocketClient
	private $socketMap = []; // SocketClient -> Client
	private $clients = array(); // Connected clients
	private $socketClients = [];
	private $run     = TRUE;    // Run the socket loop while true

	public function clientConnected(SocketClient $sc) {
		$host = $sc->getAddress();
		Log::info("New client ".$sc->getId()." connected from host: ".$host);
		$c = new Client($this);
		$this->socketClients[$sc->getId()] = $sc;
		$this->clients[$c->getId()] = $c;

		$this->clientMap[$c->getId()] = $sc->getId();
		$this->socketMap[$sc->getId()] = $c->getId();
		$c->message('{bWelcome to the club!');
		$c->message('Username: ');
	}

	private function getSocketClientForClient(Client $c) {
		$scId = $this->clientMap[$c->getId()];
		return $this->socketClients[$scId];
	}

	private function getClientForSocketClient(SocketClient $sc) {
		$cId = $this->socketMap[$sc->getId()];
		return $this->clients[$cId];
	}


	// Already happened, no request.
	public function clientDisconnected(SocketClient $sc) {
		// If client isn't already gone, kill it
		if (isset($this->socketClients[$sc->getId()])) {
			Log::info("Client disconnected: ".$sc->getId());
			$client = $this->getClientForSocketClient($sc);
			unset($this->socketClients[$sc->getId()]);
			unset($this->clientMap[$client->getId()]);
			unset($this->socketMap[$sc->getId()]);
			unset($this->clients[$client->getId()]);
			$client->connectionIsGone();
		}
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

	public function disconnectClient(Client $client) {
		$socketClient = $this->getSocketClientForClient($client);
		$socketClient->close();
	}

	public function sendMessageToClient($message, Client $client) {
		try {
			$socketClient = $this->getSocketClientForClient($client);
			$socketClient->write($message);
		}
		catch (SocketException $e) {
			Log::info("Caught socket exception writing to Client ".$client->getId());
			$client->disconnect();
		}
	}


} // class Server
