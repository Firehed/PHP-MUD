<?php

class DisconnectClientException extends Exception {
	public function __toString() {
		return $this->getMessage();
	} // function __toString
} // class DisconnectClientException

class SocketException extends Exception {
} // class SocketException
