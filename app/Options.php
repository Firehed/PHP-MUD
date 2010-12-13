<?php

$opts = getopt('a:hp:qv', array('help'));

if (isset($opts['h']) || isset($opts['help'])) {
	echo <<<HELP
Startup options:

	-a address
		Override the default address in config.php
	-h, --help
		Display this help screen and exit
	-p port
		Override the default port in config.php
	-q
		Quiet mode: suppress all errors
	-v
		Verbose mode: display info-level errors
	-vv
		Very verbose mode: also display debug-level errors

HELP;
exit;
}

if (isset($opts['a']))
	$address = $opts['a'];

if (isset($opts['p']))
	$port = $opts['p'];

define('ADDRESS', $address);

define('PORT', $port);

define('QUIET', isset($opts['q']));

define('VERBOSE', isset($opts['v']));

define('DEBUG', isset($opts['v']) && count($opts['v']) == 2);

