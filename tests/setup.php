<?php

use Bitbang\Http\Tests;
use Tester\Helpers;

require __DIR__ . '/server/BackgroundProcess.php';


echo "\n";
if (defined('HHVM_VERSION')) {
	echo "# HTTP server cannot start under HHVM, run Tester by Zend PHP.\n";
} else {
	$config = parse_ini_file(__DIR__ . '/server.ini', TRUE)['listen'];

	echo "# Starting HTTP server for tests on $config[address]:$config[port]... ";
	$server = new Tests\BackgroundProcess;
	$server->start(Helpers::escapeArg(PHP_BINARY) . " -S $config[address]:$config[port] " . Helpers::escapeArg(__DIR__ . '/server/index.php'));
	putenv("TESTS_HTTP_LISTEN=$config[address]:$config[port]");

	register_shutdown_function(function() use ($server) {
		$server->terminate();
	});

}
echo "\n";
