<?php

namespace Bitbang\Http\Clients;

use Bitbang\Http;


/**
 * Caching for HTTP clients.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class CachedClient extends Http\Sanity implements Http\IClient
{
	/** @var Http\ICache */
	private $cache;

	/** @var Http\IClient */
	private $client;

	/** @var bool */
	private $greedyCaching = FALSE;

	/** @var callable|NULL */
	private $onResponse;


	/**
	 * @param  Http\ICache
	 * @param  Http\IClient
	 */
	public function __construct(Http\ICache $cache, Http\IClient $client)
	{
		$this->cache = $cache;
		$this->client = $client;
	}


	/**
	 * @return Http\IClient
	 */
	public function getInnerClient()
	{
		return $this->client;
	}


	/**
	 * @param  bool  if TRUE, every response will be cached and never re-checked on server
	 * @return self
	 */
	public function setGreedyCaching($greedy)
	{
		$this->greedyCaching = (bool) $greedy;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function getGreedyCaching()
	{
		return $this->greedyCaching;
	}


	/**
	 * @return Http\Response
	 *
	 * @throws Http\BadResponseException
	 */
	public function request(Http\Request $request)
	{
		$request = clone $request;

		$cacheKey = implode('.', [
			$request->getMethod(),
			$request->getUrl(),

			/** @todo This should depend on Vary: header */
			$request->getHeader('Accept'),
			$request->getHeader('Accept-Encoding'),
			$request->getHeader('Authorization')
		]);

		if ($cached = $this->cache->load($cacheKey)) {
			if ($this->greedyCaching) {
				$cached = clone $cached;
				$this->onResponse && call_user_func($this->onResponse, $cached);
				return $cached;
			}

			/** @var $cached Http\Response */
			if ($cached->hasHeader('Last-Modified')) {
				$request->addHeader('If-Modified-Since', $cached->getHeader('Last-Modified'));
			} elseif ($cached->hasHeader('ETag')) {
				$request->addHeader('If-None-Match', $cached->getHeader('ETag'));
			}
		}

		$response = $this->client->request($request);

		if ($this->isCacheable($response) || $this->greedyCaching) {
			$this->cache->save($cacheKey, clone $response);
		}

		if (isset($cached) && $response->getCode() === Http\Response::S304_NOT_MODIFIED) {
			$cached = clone $cached;

			/** @todo Should be responses somehow combined into one? */
			$response = $cached->setPrevious($response);
		}

		$this->onResponse && call_user_func($this->onResponse, $response);

		return $response;
	}


	/**
	 * @param  callable|NULL function(Request $request)
	 * @return self
	 */
	public function onRequest($callback)
	{
		$this->client->onRequest($callback);
		return $this;
	}


	/**
	 * @param  callable|NULL function(Response $response)
	 * @return self
	 */
	public function onResponse($callback)
	{
		$this->client->onResponse(NULL);
		$this->onResponse = $callback;
		return $this;
	}


	/**
	 * @return bool
	 */
	protected function isCacheable(Http\Response $response)
	{
		/** @todo Do it properly. Vary:, Pragma:, TTL...  */
		if (!$response->isCode(200)) {
			return FALSE;
		} elseif (preg_match('#max-age=0|must-revalidate#i', $response->getHeader('Cache-Control', ''))) {
			return FALSE;
		}

		return $response->hasHeader('ETag') || $response->hasHeader('Last-Modified');
	}

}
