<?php

namespace Bitbang\Http\Clients;

use Bitbang\Http;


/**
 * Ancestor for HTTP clients. Cares about redirecting and debug events.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
abstract class AbstractClient extends Http\Sanity implements Http\IClient
{
	/** @var int[]  will follow Location header on these response codes */
	public $redirectCodes = [
		Http\Response::S301_MOVED_PERMANENTLY,
		Http\Response::S302_FOUND,
		Http\Response::S307_TEMPORARY_REDIRECT,
	];

	/** @var int  maximum redirects per request*/
	public $maxRedirects = 5;

	/** @var callable|NULL */
	private $onRequest;

	/** @var callable|NULL */
	private $onResponse;


	/**
	 * @return Http\Response
	 *
	 * @throws Http\BadResponseException
	 */
	public function request(Http\Request $request)
	{
		$request = clone $request;

		$counter = $this->maxRedirects;
		$previous = NULL;
		do {
			$this->setupRequest($request);

			$this->onRequest && call_user_func($this->onRequest, $request);
			$response = $this->process($request);
			$this->onResponse && call_user_func($this->onResponse, $response);

			$previous = $response->setPrevious($previous);

			if ($counter > 0 && in_array($response->getCode(), $this->redirectCodes) && $response->hasHeader('Location')) {
				/** @todo Use the same HTTP $method for redirection? Set $content to NULL? */
				$request = new Http\Request(
					$request->getMethod(),
					$response->getHeader('Location'),
					$request->getHeaders(),
					$request->getContent()
				);

				$counter--;
				continue;
			}
			break;

		} while (TRUE);

		return $response;
	}


	/**
	 * @param  callable|NULL function(Request $request)
	 * @return self
	 */
	public function onRequest($callback)
	{
		$this->onRequest = $callback;
		return $this;
	}


	/**
	 * @param  callable|NULL function(Response $response)
	 * @return self
	 */
	public function onResponse($callback)
	{
		$this->onResponse = $callback;
		return $this;
	}


	protected function setupRequest(Http\Request $request)
	{
		$request->addHeader('Expect', '');
	}


	/**
	 * @return Http\Response
	 *
	 * @throws Http\BadResponseException
	 */
	abstract protected function process(Http\Request $request);

}
