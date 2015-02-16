<?php

namespace Bitbang\Http\Tests;

use Tester;


/**
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class BackgroundProcess
{
	/** @var self[] */
	private static $instances = [];

	/** @var resource */
	private $proc;

	/** @var resource */
	private $stdout;

	/** @var resource */
	private $stderr;


	/**
	 * @param  string  command to run on background
	 * @param  string
	 * @param  string
	 */
	public function start($command, $stdout = NULL, $stderr = NULL)
	{
		self::$instances[] = $this;

		$this->proc = @proc_open(
			$command,
			[
				['pipe', 'r'],
				$stdout === NULL ? ['pipe', 'w'] : ['file', $stdout, 'wb'],
				$stderr === NULL ? ['pipe', 'w'] : ['file', $stderr, 'wb'],
			],
			$pipes,
			__DIR__,
			NULL,
			['bypass_shell' => TRUE]
		);

		if ($this->proc === FALSE) {
			throw new \RuntimeException(error_get_last()['message']);
		}

		/* @todo: Check that process didn't hang up. */

		fclose($pipes[0]);
		isset($pipes[1]) && $this->stdout = $pipes[1];
		isset($pipes[2]) && $this->stderr = $pipes[2];
	}


	public function terminate()
	{
		proc_terminate($this->proc, 2);
		proc_terminate($this->proc, 9);  # Solves job hanging on Tracis-CI PHP 5.4. Cannot reproduce.
	}

}
