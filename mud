#!/usr/bin/php
<?php

include './config.php';
include './app/Options.php';
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

Server::start(ADDRESS, PORT);
