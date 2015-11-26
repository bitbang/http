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
	/** @var int[]|NULL  follow Location header on these response codes, NULL always, empty array never */
	public $redirectCodes;

	/** @var int  maximum redirects per request */
	public $maxRedirects = 20;

	/** @var callable|NULL */
	private $onRequest;

	/** @var callable|NULL */
	private $onResponse;


	/**
	 * @return Http\Response
	 *
	 * @throws Http\BadResponseException
	 * @throws Http\RedirectLoopException
	 */
	public function process(Http\Request $request)
	{
		$request = clone $request;

		$counter = $this->maxRedirects;
		$previous = NULL;
		do {
			$this->setupRequest($request);

			$this->onRequest && call_user_func($this->onRequest, $request);
			$response = $this->processRequest($request);
			$this->onResponse && call_user_func($this->onResponse, $response);

			$previous = $response->setPrevious($previous);

			$isRedirectCode = $this->redirectCodes === NULL || in_array($response->getCode(), $this->redirectCodes);
			if ($isRedirectCode && $response->hasHeader('Location')) {
				if ($counter < 1) {
					throw new Http\RedirectLoopException("Maximum redirect count ($this->maxRedirects) achieved.");
				}
				$counter--;

				/** @todo Use the same HTTP $method for redirection? Set $body to NULL? */
				$request = new Http\Request(
					$request->getMethod(),
					Http\Helpers::absolutizeUrl($request->getUrl(), $response->getHeader('Location')),
					$request->getHeaders(),
					$request->getBody()
				);
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
	abstract protected function processRequest(Http\Request $request);


	/** @deprecated */
	public function request(Http\Request $request)
	{
		return $this->process($request);
	}

}
