<?php

class Client {

	const State_New           = 1; // Just connected
	const State_Login         = 2; // Needs to enter password
	const State_Logged_In     = 3; // Logged in
	const State_Registering   = 4; // In the registration process
	const State_Disconnecting = 5;
	const State_Disconnected  = 6;

	private static $count = 0; // Number of connected clients

	private $id;
	private $echo                = true;  // Keep track of telnet echo status
	private $failedLoginAttempts = 0;     // Failed login attempts on this connection
	private $messageSent         = false; // Whether anything has been sent to the client in this loop
	private $position;                    // Position in server's client array
	private $state;                       // Client state
	private $user;                        // User object associated with client

	private $server;
	public function getServer() {
		return $this->server;
	}

	public function __construct(Server $server) {
		$this->id = md5(spl_object_hash($this));
		$this->server = $server;
		$this->position = ++self::$count;
		$this->state = self::State_New;
	} // function __construct

	public function getId() {
		return $this->id;
	}

	public function __get($key) {
		if (isset($this->$key))
			return $this->$key;

		throw new Exception("Invalid property $key in Client object.");
	} // function __get

	public function connectionIsGone() {
		$this->state = self::State_Disconnected;
	}

	public function disconnect() {
		// Ignore duplicate calls
		if (self::State_Disconnecting === $this->state) {
			return;
		}
		if (self::State_Disconnected === $this->state) {
			// weird, but log and ignore gracefully
			Log::info("Client was told to disconnect after already disconnected ".$this->getId());
			return;
		}

		$this->state = self::State_Disconnecting;
		$this->server->disconnectClient($this);
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

	public function handleInput($input) {
		$this->messageSent = false;
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
		if ($this->state == self::State_Disconnecting ||
		$this->state == self::State_Disconnected)
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
	
	// Send string with no additional formatting (CRLF, etc)
	private function send($message) {
		$this->server->sendMessageToClient($message, $this);
	} // function send

} // class Client
