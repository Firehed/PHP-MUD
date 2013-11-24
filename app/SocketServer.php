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
	private $domain = AF_INET; // Socket domain
	private $id;
	private $socket;           // Main server socket
	private $started = false;  // Are we listening?
	private $timeoutSec = 0;   // How long should socket wait before timeout?
	private $timeoutUsec = 100000; // above, microsecond component
	private $type = SOCK_STREAM; // Socket type

	public function __construct(SocketServerDelegate $delegate) {
		$this->delegate = $delegate;
		$this->id = spl_object_hash($this);
	}

	public function __destruct() {
		$this->close();
	}

	public function getId() {
		return $this->id;
	}

	/**
	 * Set timeout of activity poll, in seconds
	 * Defaults to 0.1
	 * @param float $timeout
	 * @return this
	 */
	public function setTimeout($timeout) {
		if (!is_int($timeout) && !is_float($timeout)) {
			throw new Exception(__METHOD__." must be passed a positive number");
		}
		if ($timeout <= 0) {
			throw new Exception(__METHOD__." must be passed a positive number");
		}
		$this->timeoutSec = (int) $timeout; // interger part (implicit floor)
		$this->timeoutUsec = $timeout * 1000000 % 1000000; // fractional part, µs
		return $this;
	}

	// Todo: abstract these better
	public function setDomain($domain) {
		switch ($domain) {
		case AF_INET:
		case AF_INET6:
		case AF_UNIX:
			$this->domain = $domain;
			return $this;
		default:
			throw new Exception("Domain must be one of AF_INET, AF_INET6, AF_UNIX");
		}
	}

	// Todo: abstract these better
	public function setType($type) {
		switch ($type) {
		case SOCK_STREAM:
			$this->type = $type;
			return $this;
		case SOCK_DGRAM:
		case SOCK_SEQPACKET:
		case SOCK_RAW:
		case SOCK_RDM:
			throw new Exception("Not sure if that type is safe, rejecting");
		default:
			throw new Execption("Type must be one of SOCK_XXX constants");
		}
	}

	public function start($address, $port) {
		if ($this->domain == AF_UNIX && file_exists($address)) @unlink($address);
		$this->address = $address;
		$this->port = $port;
		$socket = socket_create($this->domain, $this->type, 0);
		socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_set_nonblock($socket);

		if (!socket_bind($socket, $address, $port)) {
			throw new Exception("Can't bind to $address:$port.");
		}

		if (!socket_listen($socket)) {
			throw new Exception("Could not start listening");
		}
		$this->socket = $socket;
		$this->started = true;
		return $this;
	}

	public function close() {
		if (!$this->started) {
			return;
		}
		foreach ($this->clients as $socketClient) {
			$socketClient->close();
		}

		// Clean up all clients first?
		socket_close($this->socket);
		if (AF_UNIX == $this->domain) {
			unlink($this->address);
		}
		$this->started = false;
	}

	/**
	 * @return resource or null
	 */
	private function accept() {
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
		if (!socket_select($sockets, $write, $except, $this->timeoutSec,
			$this->timeoutUsec)) {
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
