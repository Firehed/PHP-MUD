<?php

class Client {

	const State_New           = 1; // Just connected
	const State_Login         = 2; // Needs to enter password
	const State_Logged_In     = 3; // Logged in
	const State_Registering   = 4; // In the registration process
	const State_Disconnecting = 5;

	private static $count = 0; // Number of connected clients

	private $echo                = true;  // Keep track of telnet echo status
	private $failedLoginAttempts = 0;     // Failed login attempts on this connection
	private $host;                        // Socket client host (IP address, usually)
	private $messageSent         = false; // Whether anything has been sent to the client in this loop
	private $position;                    // Position in server's client array
	private $socket;                      // Socket resource
	private $state;                       // Client state
	private $user;                        // User object associated with client

	private $server;
	public function getServer() {
		return $this->server;
	}

	public function __construct(SocketClient $socket, Server $server) {
		$this->server = $server;
		$this->socket = $socket;
		$this->position = ++self::$count;
		$this->state = self::State_New;

		$this->host = $this->socket->getAddress();
		Log::info("Client connected from $this->host.");

		$this->message('{bWelcome to the club!');
		$this->message('Username: ');
	} // function __construct

	public function __get($key) {
		if (isset($this->$key))
			return $this->$key;

		throw new Exception("Invalid property $key in Client object.");
	} // function __get

	public function disconnect() {
		$this->state = self::State_Disconnecting;
		$this->socket->close();
		$this->server->removeClient($this);
		Log::info("Client disconnected from $this->host.");
	} // function disconnect

	public function echoOff() {
		if ($this->echo) {
			$this->send(chr(255).chr(251).chr(1));
			$this->echo = false;
		}
	} // function echoOff

	public function echoOn() {
		if (!$this->echo) {
			$this->send(chr(255).chr(252).chr(1));
			$this->echo = true;
		}
	} // function echoOn

	public function handleInput() {
		$this->messageSent = false;
		try {
			$input = trim($this->socket->read());
			if (substr($input,0,1) == chr(255)) // Ignore Telnet IAC commands, they should not be parsed!
				return;
		}
		catch (SocketException $e) {
			$this->disconnect();
			return;
		}

		if (self::State_Logged_In != $this->state) {
			$this->handleLogin($input);
			return;
		}

		Actions::perform($this, $input);
		$this->prompt();
	} // function handleInput

	public function handleLogin($input) {
		switch ($this->state) {
			case self::State_New:
				$this->user = new User($input);
				// Todo: detect concurrent logins
				if ($this->user->registered) {
					$this->state = self::State_Login;
					$this->message('Password: ');
					$this->echoOff();
				}
				else {
					$this->state = self::State_Registering;
					$this->message("Welcome, {$this->user->name}. Please set a password.");
					$this->echoOff();
				}
			return;

			case self::State_Registering:
				$this->user->register($input);
				$this->echoOn();
				$this->message("Thanks for registering. You're in.");
				$this->state = self::State_Logged_In;
			return;

			case self::State_Login:
				if ($this->user->login($input)) {
					$this->echoOn();
					$this->message('Login successful!');
					$this->state = self::State_Logged_In;
					$this->prompt();
				}
				else {
					if (++$this->failedLoginAttempts >= 3)
						throw new DisconnectClientException('Too many failed login attempts.');

					$this->message('Invalid password. Try again.');
					$this->message('Password: ');
				}
			return;
		}
	} // function handleLogin

	public function message($message) {
		if ($this->state == self::State_Disconnecting)
			return;

		$sol = ($this->messageSent || !$this->echo) ? "\n\r" : '';
		$this->send($sol . color($message));
		$this->messageSent = true;
	} // function message

	public function prompt() {
		$this->message("{$this->user->name}'s prompt ---> ");
	} // function prompt

	public function quit() {
		$this->message('{r** DISCONNECTING **');
		$this->disconnect();
	} // function quit
	
	private function send($message) {
		try {
			$this->socket->write($message);
		}
		catch (SocketException $e) {
			$this->disconnect();
		}
	} // function send

} // class Client
