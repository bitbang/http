<?php

namespace Bitbang\Http\Coders;

use Bitbang\Http;


/**
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class DefaultCoder implements Http\ICoder
{
	use Http\Strict;

	public function encode(Http\Request $request, $body)
	{
		if (is_array($body)) {
			$body = http_build_query($body);
			$request->setHeader('Content-Type', 'application/x-www-form-urlencoded');
			$request->setHeader('Content-Length', strlen($body));
		}

		return $body;
	}


	/**
	 * @param  Http\Response $response
	 * @return string
	 */
	public function decode(Http\Response $response)
	{
		return $response->getBody();
	}

}
