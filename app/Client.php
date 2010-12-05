<?php

class Client {
	const State_New           = 1; // Just connected
	const State_Login         = 2; // Needs to enter password
	const State_Logged_In     = 3; // Logged in
	const State_Registering   = 4; // In the registration process
	const State_Disconnecting = 5;
	
	private static $count = 0;
	private $socket;
	private $position;
	private $state;
	public $user;
	
	public function __construct($socket) {
		$this->socket = $socket;
		$this->position = ++self::$count;
		$this->state = self::State_New;
		
		$this->message("\033[34mWelcome to the club!");
		$this->message('Username:');
	} // function __construct
	
	public function message($message) {
		if ($this->state == self::State_Disconnecting)
			return;

		try {
			socket_write($this->socket, $message."\033[0m\n\r");
		}
		catch (SocketException $e) {
			$this->disconnect();
		}
	} // function message
	
	public function handleInput() {
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
				}
				else {
					$this->message('Invalid password. Try again.');
				}
			return;
			
		}
		
	} // function handleLogin
	
	public function getSocket() {
		return $this->socket;
	} // function getSocket
	
	public function getPosition() {
		return $this->position;
	} // function getPosition
	
	public function quit() {
		$this->message("\033[31m ** DISCONNECTING **");
		$this->disconnect();
	} // function quit
	
	public function disconnect() {
		$this->state = self::State_Disconnecting;
		socket_close($this->socket);
		Server::removeClient($this);
	} // function disconnect
	
} // class Client
