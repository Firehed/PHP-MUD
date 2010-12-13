<?php

class Tick {

	const DAY    = 0;
	const HOUR   = 1;
	const MINUTE = 2;
	const SECOND = 3;

	private static $cb_day    = array(); // Daily events
	private static $cb_hour   = array(); // Hourly events
	private static $cb_minute = array(); // Minutely events
	private static $cb_second = array(); // Secondly events
	private static $second;              // Last tick second

	public static function callback($frequency, $callback) {
		switch ($frequency) {
			case self::DAY:    self::$cb_day[]    = $callback; break;
			case self::HOUR:   self::$cb_hour[]   = $callback; break;
			case self::MINUTE: self::$cb_minute[] = $callback; break;
			case self::SECOND: self::$cb_second[] = $callback; break;
		}
	} // function callback

	public static function day() {
		foreach (self::$cb_day as $cb) {
			call_user_func($cb);
		}
	} // function day

	public static function hour() {
		foreach (self::$cb_hour as $cb) {
			call_user_func($cb);
		}

		if (self::$second % 84600 == 0)
			Tick::day();
	} // function hour

	public static function minute() {
		foreach (self::$cb_minute as $cb) {
			call_user_func($cb);
		}

		if (self::$second % 3600 == 0)
			Tick::hour();
	} // function minute

	public static function second() {
		foreach (self::$cb_second as $cb) {
			call_user_func($cb);
		}

		if (self::$second % 60 == 0)
			Tick::minute();
	} // function second

	public static function tock() {
		if (self::$second < $new=time()) {
			self::$second = $new;
			Tick::second();
		}
	} // function tock

} // class Tick
