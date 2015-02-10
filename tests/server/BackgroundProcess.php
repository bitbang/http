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


	/**
	 * @param  string  command to run on background
	 * @param  int
	 * @param  string
	 */
	public function start($command)
	{
		self::$instances[] = $this;

		$this->proc = @proc_open(
			$command,
			[['pipe', 'r'],	['pipe', 'w'], ['pipe', 'w']],
			$pipes,
			__DIR__,
			NULL,
			['bypass_shell' => TRUE]
		);

		if ($this->proc === FALSE) {
			throw new \RuntimeException(error_get_last()['message']);
		}

		/* @todo: Check that process didn't hang up. */

		list($stdin, $this->stdout, $stderr) = $pipes;
		fclose($stdin);
		fclose($stderr);
	}


	public function terminate()
	{
		proc_terminate($this->proc, 2);
		proc_terminate($this->proc, 9);  # Solves job hanging on Tracis-CI PHP 5.4. Cannot reproduce.
	}

}
