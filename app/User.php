<?php

class User {

	private static $db; // Shared database connection

	private $changed = FALSE;         // Have any properties changed?
	private $data;                    // Generic key/value store for data
	private $failedLoginAttempts = 0; // Failed login attempts on this connection

	public function __construct($username) {
		self::$db === NULL && self::$db = Database::instance(); // Init DB

		$s = self::$db->prepare('SELECT data FROM users WHERE username = ?');
		$s->bind_param('s', $username);
		$s->execute();
		$s->store_result();
		if ($s->num_rows) {
			$s->bind_result($data);
			$s->fetch();
			$this->data = unserialize($data);
		}
		else {
			$this->data = new StdClass;
			$this->name = $username;
			$this->registered = FALSE;
		}
		$s->close();
	} // function __construct

	public function __get($key) {
		return $this->data->$key;
	} // function __get

	public function __set($key, $value) {
		$this->data->$key = $value;
		$this->changed = TRUE;
	} // function __set

	public function login($password) {
		if (sha1($password.SALT) === $this->password)
			return true;

		if (++$this->failedLoginAttempts >= 3)
			throw new ClientDisconnectException('EPIC LOGIN FAIL');

		return false;
	} // function login

	public function register($password) {
		$this->password = sha1($password.SALT);
		$this->registered = TRUE;
		return $this->save();
	} // function register

	public function save() {
		if (!$this->changed) {
			return $this;
		}
		$data = serialize($this->data);
		$name = $this->name;
		$s = self::$db->prepare('INSERT INTO users (`username`, `data`) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data)');
		$s->bind_param('ss', $name, $data);
		$s->execute();
		$s->close();

		return $this;
	} // function save

} // class User
