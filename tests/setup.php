<?php

use Bitbang\Http\Tests;
use Tester\Helpers;

require __DIR__ . '/server/BackgroundProcess.php';


echo "\n";
if (defined('HHVM_VERSION')) {
	echo "# HTTP server cannot start under HHVM, run Tester by Zend PHP.\n";
} else {
	@mkdir(__DIR__ . '/temp');  # @ = directory may already exist

	$config = parse_ini_file(__DIR__ . '/server.ini', TRUE)['listen'];

	echo "# Starting HTTP server for tests on $config[address]:$config[port]... ";
	$server = new Tests\BackgroundProcess;
	$server->start(
		Helpers::escapeArg(PHP_BINARY) . " -S $config[address]:$config[port] -d always_populate_raw_post_data=-1" . Helpers::escapeArg(__DIR__ . '/server/index.php'),
		__DIR__ . '/temp/http.log',
		__DIR__ . '/temp/http.log'
	);
	echo "done\n";
	putenv("TESTS_HTTP_LISTEN=$config[address]:$config[port]");

	echo "# Starting SSL wrapper for tests on $config[address]:$config[port_ssl]... ";
	$wrapper = new Tests\BackgroundProcess;
	$wrapper->start(
		Helpers::escapeArg(PHP_BINARY) . ' ' . Helpers::escapeArg(__DIR__ . '/server/ssl-wrapper.php'),
		__DIR__ . '/temp/ssl-wrapper.log',
		__DIR__ . '/temp/ssl-wrapper.log'
	);
	echo "done\n";
	putenv("TESTS_HTTPS_LISTEN=$config[address]:$config[port_ssl]");

	register_shutdown_function(function() use ($server, $wrapper) {
		echo "\n";
		echo '# Shutting down SSL wrapper... ';
		$wrapper->terminate();
		echo "done\n";

		echo '# Shutting down HTTP server... ';
		$server->terminate();
		echo "done\n";
	});

}
echo "\n";
