<?php

namespace Bitbang\Http;


/**
 * HTTP request coder, response decoder interface.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
interface ICoder
{
	/**
	 * @param  Response
	 * @return mixed
	 */
	function decode(Response $response);

}
