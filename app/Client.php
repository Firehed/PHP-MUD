<?php

class Client {
	const State_New           = 1; // Just connected
	const State_Login         = 2; // Needs to enter password
	const State_Logged_In     = 3; // Logged in
	const State_Registering   = 4; // In the registration process
	const State_Disconnecting = 5;

	private static $count = 0; // Number of connected clients

	private $failedLoginAttempts = 0; // Failed login attempts on this connection
	private $messageSent = false;     // Whether anything has been sent to the client in this loop
	private $position;                // Position in server's client array
	private $socket;                  // Socket resource
	private $state;                   // Client state
	private $user;                    // User object associated with client

	public function __construct($socket) {
		$this->socket = $socket;
		$this->position = ++self::$count;
		$this->state = self::State_New;

		$this->message('{bWelcome to the club!');
		$this->message('Username:');
	} // function __construct

	public function __get($key) {
		if (isset($this->$key))
			return $this->$key;
		throw new Exception("Invalid property $key in Client object.");
	} // function __get

	public function disconnect() {
		$this->state = self::State_Disconnecting;
		socket_close($this->socket);
		Server::removeClient($this);
	} // function disconnect

	public function handleInput() {
		$this->messageSent = false;
		try {
			$input = trim(socket_read($this->socket, 1024));
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
					$this->message('Password:');
				}
				else {
					$this->state = self::State_Registering;
					$this->message("Welcome, {$this->user->name}. Please set a password.");
				}
			return;

			case self::State_Registering:
				$this->user->register($input);
				$this->message('Thanks for registering. You\'re in.');
				$this->state = self::State_Logged_In;
			return;

			case self::State_Login:
				if ($this->user->login($input)) {
					$this->message('Login successful!');
					$this->state = self::State_Logged_In;
					$this->prompt();
				}
				else {
					if (++$this->failedLoginAttempts >= 3)
						throw new DisconnectClientException('Too many failed login attempts.');
					$this->message('Invalid password. Try again.');
				}
			return;

		}

	} // function handleLogin

	public function message($message) {
		if ($this->state == self::State_Disconnecting)
			return;

		try {
			$sol = $this->messageSent ? "\n\r" : '';
			socket_write($this->socket, $sol . color($message) . COLOR_RESET . ' ');
			$this->messageSent = true;
		}
		catch (SocketException $e) {
			$this->disconnect();
		}
	} // function message

	public function prompt() {
		$this->message("{$this->user->name}'s prompt --->");
	} // function prompt

	public function quit() {
		$this->message('{r** DISCONNECTING **');
		$this->disconnect();
	} // function quit

} // class Client
