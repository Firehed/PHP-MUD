<?php

class User extends ORM {

	protected static $PK    = 'username';
	protected static $table = 'users';

	protected function  _load($PK, MySQLI_Stmt $s) {
		if ($s->num_rows) {
			$s->bind_result($data);
			$s->fetch();
			$this->data = unserialize($data);
		}
		else {
			$this->data = new StdClass;
			$this->name = $PK;
			$this->registered = FALSE;
		}
	} // function _load

	public function login($password) {
		return sha1($password.SALT) === $this->password;
	} // function login

	public function register($password) {
		$this->password = sha1($password.SALT);
		$this->registered = TRUE;
		return $this->save();
	} // function register

	public function save() {
		if (!$this->changed)
			return $this;

		$data = serialize($this->data);
		$name = $this->name;
		$s = self::$db->prepare('INSERT INTO users (`username`, `data`) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data)');
		$s->bind_param('ss', $name, $data);
		$s->execute();
		$s->close();

		return $this;
	} // function save

} // class User
