<?php

namespace Bitbang\Http;


/**
 * Undefined member access check. Stolen from Nette\Object (http://nette.org).
 */
abstract class Sanity
{
	/**
	 * @param  string
	 *
	 * @throws LogicException
	 */
	public function & __get($name)
	{
		throw new LogicException('Cannot read an undeclared property ' . get_class($this) . "::\$$name.");
	}


	/**
	 * @param  string
	 * @param  mixed
	 * @throws LogicException
	 */
	public function __set($name, $value)
	{
		throw new LogicException('Cannot write to an undeclared property ' . get_class($this) . "::\$$name.");
	}

}
