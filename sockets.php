#!/usr/bin/php
<?php

include './config.php';
include './app/Log.php';
include './app/Exceptions.php';
include './app/Startup.php';
include './app/Tick.php';
include './app/Colors.php';
include './app/Actions.php';
include './app/Client.php';
include './app/Database.php';
include './app/Server.php';
include './app/User.php';

register_tick_function(array('Tick','tock'));
declare(ticks = 10); // The Loop runs pretty darn often, let's not overload things

Server::start($address, $port);
