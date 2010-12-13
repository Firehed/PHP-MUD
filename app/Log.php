<?php
class Log {
	public static function debug($text) {
		if (DEBUG)
			self::write('DBG', $text);
	} // function debug

	public static function error($text) {
		self::write('ERR', $text);
	} // function error

	public static function info($text) {
		self::write('NFO', $text);
	} // function info
	
	private static function write($prefix, $text) {
		echo date('Y-m-d H:i:s') . " [$prefix] $text\n";
	} // function write

}
