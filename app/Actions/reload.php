<?php

class reload implements Action {

	public static function ok(Client $client) {
		return true;
	} // function ok

	public static function run(Client $client, $cmd, $arg) {
		if (strtolower($cmd) != 'reload' || strtolower($arg) != 'now') {
			$client->message('Reload must be run as "reload now"');
			return;
		}
		if (!function_exists('runkit_import')) {
			$client->message('The server must have the {1runkit{0 extension installed.');
			return;
		}
		if (!class_exists('ReflectionClass')) {
			$client->message('The server must have the {1Reflection{0 extesion installed.');
			return;
		}
		Log::info("Reload performed by {$client->user->name} from {$client->host}.");
		$list = include './app/Actions/List';
		foreach ($list['files'] as $file) {
			try {
				runkit_import($file, RUNKIT_IMPORT_FUNCTIONS | RUNKIT_IMPORT_CLASSES | RUNKIT_IMPORT_OVERRIDE);
			} catch (ErrorException $e) {
				// Ignore - runkit can't import itself
			}
		}
		$RC = new ReflectionClass('Actions');
		$a = $RC->getProperty('actions');
		$a->setAccessible(true);
		$a->setValue($list['actions']);
		$a->setAccessible(false);
		unset($RC);
		$client->message('Reload complete!');
	} // function run

} // class reload
