<?php

class User extends ORM {

	protected static $PK    = 'username';
	protected static $table = 'users';

	protected function _load($PK, MySQLI_Stmt $s) {
		if ($s->num_rows) {
			$s->bind_result($username, $data);
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
		return crypt($password, $this->salt) === $this->password;
	} // function login

	public function register($password) {
		$letters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9','.','/');
		$salt = '';
		for ($i = 0; $i < 22; $i++) {
			shuffle($letters);
			$salt .= current($letters);
		}
		$this->salt     = '$2a$07$' . $salt;
		$this->password = crypt($password, $this->salt);
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
