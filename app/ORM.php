<?php

abstract class ORM {

	protected static $db; // Shared database connection

	protected $changed = FALSE; // Have any properties changed?
	protected $data;            // Generic key/value store for data

	public function __construct($PK) {
		self::$db === NULL && self::$db = Database::instance(); // Init DB
		$s = self::$db->prepare('SELECT * FROM ' . static::$table . ' WHERE ' . static::$PK . ' = ?');
		$s->bind_param('s', $PK);
		$s->execute();
		$s->store_result();
		$this->_load($PK, $s);
		$s->close();
	} // function __construct

	abstract protected function _load($PK, MySQLI_Stmt $s);

	public function __get($key) {
		return $this->data->$key;
	} // function __get

	public function __set($key, $value) {
		$this->data->$key = $value;
		$this->changed = TRUE;
	} // function __set

} // class ORM
