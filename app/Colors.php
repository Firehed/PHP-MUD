<?php

define('COLOR_RESET',      "\033[0m");    // Reset
define('COLOR_DK_BLACK',   "\033[0;30m"); // Black
define('COLOR_DK_RED',     "\033[0;31m"); // Red
define('COLOR_DK_GREEN',   "\033[0;32m"); // Green
define('COLOR_DK_YELLOW',  "\033[0;33m"); // Yellow
define('COLOR_DK_BLUE',    "\033[0;34m"); // Blue
define('COLOR_DK_MAGENTA', "\033[0;35m"); // Magenta
define('COLOR_DK_CYAN',    "\033[0;36m"); // Cyan
define('COLOR_DK_WHITE',   "\033[0;37m"); // White
define('COLOR_LT_BLACK',   "\033[1;30m"); // Light Black
define('COLOR_LT_RED',     "\033[1;31m"); // Light Red
define('COLOR_LT_GREEN',   "\033[1;32m"); // Light Green
define('COLOR_LT_YELLOW',  "\033[1;33m"); // Light Yellow
define('COLOR_LT_BLUE',    "\033[1;34m"); // Light Blue
define('COLOR_LT_MAGENTA', "\033[1;35m"); // Light Magenta
define('COLOR_LT_CYAN',    "\033[1;36m"); // Light Cyan
define('COLOR_LT_WHITE',   "\033[1;37m"); // Light White

function color($str) {
	return preg_replace_callback('/({.)/', function($m) {
		switch ($m[0][1]) {
			case 'r':
			case '1':
				return COLOR_DK_RED;
			case 'g':
			case '2':
				return COLOR_DK_GREEN;
			case 'y':
			case '3':
				return COLOR_DK_YELLOW;
			case 'b':
			case '4':
				return COLOR_DK_BLUE;
			case 'm':
			case '5':
				return COLOR_DK_MAGENTA;
			case 'c':
			case '6':
				return COLOR_DK_CYAN;
			case 'w':
			case '7':
				return COLOR_DK_WHITE;
			case 'd':
			case '8':
				return COLOR_DK_BLACK;

			case 'R':
			case '!':
				return COLOR_LT_RED;
			case 'G':
			case '@':
				return COLOR_LT_GREEN;
			case 'Y':
			case '#':
				return COLOR_LT_YELLOW;
			case 'B':
			case '$':
				return COLOR_LT_BLUE;
			case 'M':
			case '%':
				return COLOR_LT_MAGENTA;
			case 'C':
			case '^':
				return COLOR_LT_CYAN;
			case 'W':
			case '&':
				return COLOR_LT_WHITE;
			case 'D':
			case '*':
				return COLOR_LT_BLACK;

			case '{':
				return '{';

			default:
				return COLOR_RESET;
		}
	}, $str);
}
