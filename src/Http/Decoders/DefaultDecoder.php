<?php

namespace Bitbang\Http\Decoders;

use Bitbang\Http;


/**
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class DefaultDecoder extends Http\Sanity implements Http\IDecoder
{

	/**
	 * @param  Http\Response $request
	 * @return string
	 */
	public function decode(Http\Response $request)
	{
		return $request->getBody();
	}

}
