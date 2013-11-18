<?php

class SocketClient {

	private $socket;

	/**
	 * @param resource
	 */
	public function __construct($socket) {
		$this->socket = $socket;
	}

	public function getAddress() {
		socket_getpeername($this->socket, $address);
		return $address;
	}

	public function close() {
		socket_close($this->socket);
	}

	public function read($len = 1024) {
		return socket_read($this->socket, $len);
	}

	public function write($message) {
		socket_write($this->socket, $message);
	}


}
