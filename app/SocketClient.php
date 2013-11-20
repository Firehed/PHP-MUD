<?php

interface SocketClientDelegate {
	public function clientClosed (SocketClient $sc);
}

// Fixme: no-op any connections if the socket is closed. there might be a need
// for socket_shutdown, probably requires more digging into unix socket land
class SocketClient {

	private $id;
	private $socket;
	private $delegate;

	/**
	 * @param SocketClientDelegate $delegate
	 * @param resource $socket
	 */
	public function __construct(SocketClientDelegate $delegate, $socket) {
		$this->delegate = $delegate;
		$this->socket = $socket;
		$this->id = spl_object_hash($this);
	}

	public function getId() {
		return $this->id;
	}

	public function getAddress() {
		socket_getpeername($this->socket, $address);
		return $address;
	}

	public function close() {
		socket_close($this->socket);
		$this->delegate->clientClosed($this);
	}

	public function read($len = 1024) {
		return socket_read($this->socket, $len);
	}

	public function write($message) {
		socket_write($this->socket, $message);
	}

}
