<?php

class Actions {

	private static $actions = array();

	public static function perform(Client $client, $input) {
		$input = trim($input);
		if (!$input)
			return;

		$cmd = strstr($input, ' ', true) ? : $input;
		foreach (self::$actions as $action) {
			if (0 === strpos($action, $cmd) && $action::ok($client)) {
				$arg = trim(strstr($input, ' '));
				$action::run($client, $cmd, $arg);
				return;
			}
		}
		$client->message('Unknown command :(');
	} // function perform

	public static function register() {
		$list = include './app/Actions/List';
		foreach ($list['files'] as $file) {
			include $file;
		}
		self::$actions = $list['actions'];
		//self::$actions = include './app/Actions/List';
	} // function register

} // class Actions

interface Action {
	public static function ok(Client $client);
	public static function run(Client $client, $cmd, $arg);
} // interface Action
