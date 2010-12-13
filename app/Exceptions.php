<?php

class DisconnectClientException extends Exception {
	public function __toString() {
		return $this->getMessage();
	} // function __toString
}

class SocketException extends Exception {}

