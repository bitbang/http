<?php

namespace Bitbang\Http;


/**
 * HTTP response decoder interface.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
interface IDecoder
{
	/**
	 * @param  Response
	 * @return mixed
	 */
	function decode(Response $response);

}
