<?php

set_time_limit(0);
error_reporting(-1);

set_error_handler(function($no,$str,$f,$l) {
	Log::debug(print_r(func_get_args(), true));
	// Handle socket exceptions differently
	if (strstr($str, 'socket_'))
		throw new SocketException($str, $no);

	throw new ErrorException($str,0,$no,$f,$l);
}, -1);
