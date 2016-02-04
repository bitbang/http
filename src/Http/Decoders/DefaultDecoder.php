<?php

namespace Bitbang\Http\Decoders;

use Bitbang\Http;


/**
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class DefaultDecoder implements Http\IDecoder
{
	use Http\Strict;

	/**
	 * @param  Http\Response $request
	 * @return string
	 */
	public function decode(Http\Response $request)
	{
		return $request->getBody();
	}

}
