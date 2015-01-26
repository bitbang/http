<?php

/**
 * @author  Miloslav Hůla (https://github.com/milo)
 */

class HttpServer
{
	/** @var self */
	private static $instance;

	/** @var resource */
	private $proc;

	/** @var resource */
	private $stdout;


	public function start($address, $port, $index)
	{
		echo "\n# Starting HTTP server for tests on $address:$port... ";

		if (self::$instance !== NULL) {
			throw new \LogicException('Server can run only once, but is already running.');
		}
		self::$instance = $this;

		$this->proc = @proc_open(
			Tester\Helpers::escapeArg(PHP_BINARY) . " -S $address:$port " . Tester\Helpers::escapeArg($index),
			[['pipe', 'r'],	['pipe', 'w'], ['pipe', 'w']],
			$pipes,
			__DIR__,
			NULL,
			['bypass_shell' => TRUE]
		);

		if ($this->proc === FALSE) {
			throw new \RuntimeException(error_get_last()['message']);
		}

		list($stdin, $this->stdout, $stderr) = $pipes;
		fclose($stdin);
		fclose($stderr);

		putenv("TESTS_HTTP_LISTEN=$address:$port");

		echo "done\n\n";

		register_shutdown_function(function() {
			proc_terminate($this->proc);
		});
	}

}

$config = parse_ini_file(__DIR__ . '/server.ini', TRUE)['listen'];
$server = new HttpServer;
$server->start($config['address'], $config['port'], __DIR__ . '/server/index.php');
