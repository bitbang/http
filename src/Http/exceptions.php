<?php


/**
 * All exceptions at one place. Whole library does not throw anything else.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */

namespace Bitbang\Http {

	/**
	 * Marker interface only.
	 */
	interface IException
	{
	}


	/**
	 * Wrong algorithm. Library is used in a wrong way. Application code should be changed.
	 */
	class LogicException extends \LogicException implements IException
	{
	}


	/**
	 * Unpredictable situation occurred.
	 */
	abstract class RuntimeException extends \RuntimeException implements IException
	{
	}


	/**
	 * HTTP response is somehow wrong and cannot be processed.
	 */
	class BadResponseException extends RuntimeException
	{
	}


	/**
	 * Redirect loop detected.
	 */
	class RedirectLoopException extends BadResponseException
	{
	}

}


namespace Bitbang\Http\Storages {

	use Bitbang\Http;

	/**
	 * Directory is missing.
	 */
	class MissingDirectoryException extends Http\RuntimeException
	{
	}

}
