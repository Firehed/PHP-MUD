<?php

class Database {

	private static $instance;

	private function __construct() {
		if (self::$instance === NULL)
			self::$instance = new MySQLi(DBHOST, DBUSER, DBPASS, DBNAME);
	} // function __construct

	public static function instance() {
		if (self::$instance === NULL)
			new Database;

		return self::$instance;
	} // function instance

} // class Database
