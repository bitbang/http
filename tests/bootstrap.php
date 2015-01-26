<?php

if (!is_file($autoload = __DIR__ . '/../vendor/autoload.php')) {
	echo "Tester not found. Install Nette Tester using 'composer update --dev'.\n";
	exit(1);
}
require $autoload;
unset($autoload);


Tester\Environment::setup();
date_default_timezone_set('UTC');


if (($listen = getenv('TESTS_HTTP_LISTEN')) === FALSE) {
	Tester\Assert::fail("Missing 'TESTS_HTTP_LISTEN' environment variable. Did you use '--setup tests/setup.php' option?");
}
define('BASE_URL', "http://$listen");
unset($listen);


function test(\Closure $f) {
	$f();
}


function getTempDir() {
	static $created;

	@mkdir($base = __DIR__ . DIRECTORY_SEPARATOR . 'temp'); // @ - may already exist
	$dir = $base . DIRECTORY_SEPARATOR . getmypid();

	if (!$created) {
		$created = TRUE;
		Tester\Helpers::purge($dir);
		register_shutdown_function(function() use ($dir) {
			Tester\Helpers::purge($dir);
			rmdir($dir);
		});
	}

	return $dir;
}
