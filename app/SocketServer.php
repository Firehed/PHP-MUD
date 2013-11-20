<?php

interface SocketServerDelegate {
	function clientConnected(SocketClient $sc);
	function clientDisconnected(SocketClient $sc);
	function clientSentMessage(SocketClient $sc, $message);
}

class SocketServer implements SocketClientDelegate {

	private $clients = [];     // SocketClient objects
	private $connections = []; // Raw client resources (required for reading)
	private $delegate;         // Delegate to receive info
	private $socket;           // Main server socket

	public function __construct(SocketServerDelegate $delegate) {
		$socket = socket_create(AF_INET, SOCK_STREAM, 0);
		socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_set_nonblock($socket);
		$this->socket = $socket;
		$this->delegate = $delegate;
	}

	public function start($address, $port) {
		if (!socket_bind($this->socket, $address, $port)) {
			throw new Exception("Can't bind to port $port.");
		}
		if (!socket_listen($this->socket)) {
			throw new Exception("Could not start listening");
		}
	}

	public function close() {
		// Clean up all clients first?
		Log::info("Closing main socket");
		socket_close($this->socket);
	}

	/**
	 * @return resource or null
	 */
	public function accept() {
		if ($raw = socket_accept($this->socket)) {
			return $raw;
		}
		// throw?
	}

	public function clientClosed(SocketClient $sc) {
		$id = $sc->getId();
		unset($this->clients[$id]);
		unset($this->connections[$id]);
		$this->delegate->clientDisconnected($sc);
	}

	public function handleReads() {
		$write = $except = null;
		// Copy socket resouces for polling
		$sockets = [$this->socket] + $this->connections;
		// Look for changes
		if (!socket_select($sockets, $write, $except, 0, 100000)) {
			// nothing to do!
			return;
		}

		foreach ($sockets as $pos => $socket) {
			// Incoming connection
			if ($socket == $this->socket) {
				$raw = $this->accept();
				$sc = new SocketClient($this, $raw);
				$id = $sc->getId();
				$this->clients[$id] = $sc;
				$this->connections[$id] = $raw;
				$this->delegate->clientConnected($sc);
			}
			// Client changed
			else {
				$sc = $this->clients[$pos];
				try {
					$message = $sc->read();
				} catch (SocketException $e) {
					// Client dropped
					$sc->close();
					continue;
				}
				// Filter out Telnet IAC commands, delegate doesn't care
				if (chr(255) != substr($message, 0, 1)) {
					$this->delegate->clientSentMessage($sc, trim($message));
				}
			}
		} // poll loop
	}

}
