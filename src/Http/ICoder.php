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
	 * @param  Request
	 * @param  mixed|NULL
	 * @return mixed
	 */
	function encode(Request $request, $body);


	/**
	 * @param  Response
	 * @return mixed
	 */
	function decode(Response $response);

}
