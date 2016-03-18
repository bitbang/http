<?php

namespace Bitbang\Http\Coders;

use Bitbang\Http;


/**
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class DefaultCoder implements Http\ICoder
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
