<?php

/** @testCase */

require __DIR__ . '/../../bootstrap.php';

use Bitbang\Http;
use Tester\Assert;


class MockClient implements Http\IClient
{
	/** @var callable */
	public $requestCallback;

	/** @var int */
	public $requestCount = 0;


	public function process(Http\Request $request)
	{
		$response = call_user_func($this->requestCallback, $request);
		$this->requestCount++;
		return $response;
	}

	public function onRequest($foo)
	{
		trigger_error('Inner onRequest called: ' . var_export($foo, TRUE), E_USER_NOTICE);
	}

	public function onResponse($foo)
	{
		trigger_error('Inner onResponse called: ' . var_export($foo, TRUE), E_USER_NOTICE);
	}

}


class MockCache implements Http\ICache
{
	private $cache = [];

	public function save($key, $value)
	{
		return $this->cache[$key] = $value;
	}

	public function load($key)
	{
		return isset($this->cache[$key]) ? $this->cache[$key] : NULL;
	}

}


class TestedCachedClient extends Http\Clients\CachedClient
{
	public function isCacheable(Http\Response $response) { return parent::isCacheable($response); }
}


class CachedClientTestCase extends Tester\TestCase
{
	/** @var TestedCachedClient */
	private $client;

	/** @var MockClient */
	private $innerClient;


	public function setup()
	{
		$cache = new MockCache;
		$this->innerClient = new MockClient;
		$this->client = new TestedCachedClient($cache, $this->innerClient);

		$this->innerClient->requestCallback = function (Http\Request $request) {
			return $request->hasHeader('If-None-Match')
				? new Http\Response(304, [], "inner-304-{$request->getBody()}")
				: new Http\Response(200, ['ETag' => '"inner"'], "inner-200-{$request->getBody()}");
		};
	}


	public function testOnRequestOnResponse()
	{
		Assert::same($this->innerClient, $this->client->getInnerClient());

		Assert::error(function() {
			Assert::same($this->client, $this->client->onRequest('callback-1'));
			Assert::same($this->client, $this->client->onResponse('callback-2'));
		}, [
			[E_USER_NOTICE, "Inner onRequest called: 'callback-1'"],
			[E_USER_NOTICE, 'Inner onResponse called: NULL'],
		]);

		$onResponseCalled = FALSE;
		Assert::error(function() use (& $onResponseCalled) {
			$this->client->onResponse(function() use (& $onResponseCalled) {
				$onResponseCalled = TRUE;
			});
		}, E_USER_NOTICE, 'Inner onResponse called: NULL');

		$this->client->process(new Http\Request('', ''));
		Assert::true($onResponseCalled);

		Assert::same(1, $this->innerClient->requestCount);
	}


	public function testNoCaching()
	{
		$this->innerClient->requestCallback = function (Http\Request $request) {
			Assert::false($request->hasHeader('ETag'));
			Assert::false($request->hasHeader('If-Modified-Since'));

			return new Http\Response(200, [], "response-{$request->getBody()}");
		};

		$response = $this->client->process(new Http\Request('', '', [], '1'));
		Assert::same('response-1', $response->getBody());
		Assert::same(1, $this->innerClient->requestCount);

		$response = $this->client->process(new Http\Request('', '', [], '2'));
		Assert::same('response-2', $response->getBody());
		Assert::same(2, $this->innerClient->requestCount);
	}


	public function testETagCaching()
	{
		$this->innerClient->requestCallback = function (Http\Request $request) {
			Assert::false($request->hasHeader('If-None-Match'));
			Assert::false($request->hasHeader('If-Modified-Since'));

			return new Http\Response(200, ['ETag' => 'e-tag'], "response-{$request->getBody()}");
		};

		$response = $this->client->process(new Http\Request('', '', [], '1'));
		Assert::same('response-1', $response->getBody());
		Assert::same(1, $this->innerClient->requestCount);


		$this->innerClient->requestCallback = function (Http\Request $request) {
			Assert::same('e-tag', $request->getHeader('If-None-Match'));
			Assert::false($request->hasHeader('If-Modified-Since'));

			return new Http\Response(304, [], "response-{$request->getBody()}");
		};
		$response = $this->client->process(new Http\Request('', '', [], '2'));
		Assert::same('response-1', $response->getBody());
		Assert::type('Bitbang\Http\Response', $response->getPrevious());
		Assert::same(304, $response->getPrevious()->getCode());
		Assert::same(2, $this->innerClient->requestCount);
	}


	public function testIfModifiedCaching()
	{
		$this->innerClient->requestCallback = function (Http\Request $request) {
			Assert::false($request->hasHeader('If-None-Match'));
			Assert::false($request->hasHeader('If-Modified-Since'));

			return new Http\Response(200, ['Last-Modified' => 'today'], "response-{$request->getBody()}");
		};

		$response = $this->client->process(new Http\Request('', '', [], '1'));
		Assert::same('response-1', $response->getBody());
		Assert::same(1, $this->innerClient->requestCount);


		$this->innerClient->requestCallback = function (Http\Request $request) {
			Assert::false($request->hasHeader('ETag'));
			Assert::same('today', $request->getHeader('If-Modified-Since'));

			return new Http\Response(304, [], "response-{$request->getBody()}");
		};

		$response = $this->client->process(new Http\Request('', '', [], '2'));
		Assert::same('response-1', $response->getBody());
		Assert::type('Bitbang\Http\Response', $response->getPrevious());
		Assert::same(304, $response->getPrevious()->getCode());
		Assert::same(2, $this->innerClient->requestCount);
	}


	public function testPreferIfModifiedAgainstETag()
	{
		$this->innerClient->requestCallback = function (Http\Request $request) {
			Assert::false($request->hasHeader('If-None-Match'));
			Assert::false($request->hasHeader('If-Modified-Since'));

			return new Http\Response(200, ['Last-Modified' => 'today', 'ETag' => 'e-tag'], "response-{$request->getBody()}");
		};

		$response = $this->client->process(new Http\Request('', '', [], '1'));
		Assert::same('response-1', $response->getBody());
		Assert::same(1, $this->innerClient->requestCount);


		$this->innerClient->requestCallback = function (Http\Request $request) {
			Assert::false($request->hasHeader('ETag'));
			Assert::same('today', $request->getHeader('If-Modified-Since'));

			return new Http\Response(304, [], "response-{$request->getBody()}");
		};

		$response = $this->client->process(new Http\Request('', '', [], '2'));
		Assert::same('response-1', $response->getBody());
		Assert::type('Bitbang\Http\Response', $response->getPrevious());
		Assert::same(304, $response->getPrevious()->getCode());
		Assert::same(2, $this->innerClient->requestCount);
	}


	public function testRepeatedRequest()
	{
		$request = new Http\Request('', '', [], 'same');

		# Empty cache
		$response = $this->client->process($request);
		Assert::same('inner-200-same', $response->getBody());
		Assert::null($response->getPrevious());
		Assert::same(1, $this->innerClient->requestCount);

		# From cache
		$response = $this->client->process($request);
		Assert::same('inner-200-same', $response->getBody());
		Assert::type('Bitbang\Http\Response', $response->getPrevious());
		Assert::same('inner-304-same', $response->getPrevious()->getBody());
		Assert::same(2, $this->innerClient->requestCount);

		# Again
		$response = $this->client->process($request);
		Assert::same('inner-200-same', $response->getBody());
		Assert::type('Bitbang\Http\Response', $response->getPrevious());
		Assert::same('inner-304-same', $response->getPrevious()->getBody());
		Assert::same(3, $this->innerClient->requestCount);
	}


	public function testGreedyCachingDisabled()
	{
		Assert::false($this->client->getGreedyCaching());

		$request = new Http\Request('', '', [], 'disabled');

		$response = $this->client->process($request);
		Assert::same('inner-200-disabled', $response->getBody());
		Assert::null($response->getPrevious());
		Assert::same(1, $this->innerClient->requestCount);

		$response = $this->client->process($request);
		Assert::same('inner-200-disabled', $response->getBody());
		Assert::type('Bitbang\Http\Response', $response->getPrevious());
		Assert::same('inner-304-disabled', $response->getPrevious()->getBody());
		Assert::same(2, $this->innerClient->requestCount);

		$response = $this->client->process($request);
		Assert::same('inner-200-disabled', $response->getBody());
		Assert::type('Bitbang\Http\Response', $response->getPrevious());
		Assert::same('inner-304-disabled', $response->getPrevious()->getBody());
		Assert::same(3, $this->innerClient->requestCount);
	}


	public function testGreedyCachingEnabled()
	{
		$this->client->setGreedyCaching(TRUE);
		Assert::true($this->client->getGreedyCaching());

		$request = new Http\Request('', '', [], 'enabled');

		$response = $this->client->process($request);
		Assert::same('inner-200-enabled', $response->getBody());
		Assert::null($response->getPrevious());
		Assert::same(1, $this->innerClient->requestCount);

		$response = $this->client->process($request);
		Assert::same('inner-200-enabled', $response->getBody());
		Assert::null($response->getPrevious());
		Assert::same(1, $this->innerClient->requestCount);

		$response = $this->client->process($request);
		Assert::same('inner-200-enabled', $response->getBody());
		Assert::null($response->getPrevious());
		Assert::same(1, $this->innerClient->requestCount);
	}


	/**
	 * Caching normally non-cacheable responses in greedy caching mode.
	 */
	public function testGreedyCachingEveryResponse()
	{
		$this->client->setGreedyCaching(TRUE);
		Assert::true($this->client->getGreedyCaching());

		$response = new Http\Response(404, [], '');
		Assert::false($this->client->isCacheable($response));

		$this->innerClient->requestCallback = function (Http\Request $request) use ($response) {
			return $response;
		};

		$response = $this->client->process(new Http\Request('GET', ''));
		Assert::same(404, $response->getCode());
		Assert::null($response->getPrevious());
		Assert::same(1, $this->innerClient->requestCount);

		$response = $this->client->process(new Http\Request('GET', ''));
		Assert::same(404, $response->getCode());
		Assert::null($response->getPrevious());
		Assert::same(1, $this->innerClient->requestCount);

		$response = $this->client->process(new Http\Request('GET', 'next'));
		Assert::same(404, $response->getCode());
		Assert::null($response->getPrevious());
		Assert::same(2, $this->innerClient->requestCount);
	}


	public function testIsCacheable()
	{
		Assert::true($this->client->isCacheable(new Http\Response(200, ['ETag' => 'tag'], '')));
		Assert::true($this->client->isCacheable(new Http\Response(200, ['Last-Modified' => 'today'], '')));

		Assert::false($this->client->isCacheable(new Http\Response(201, ['ETag' => 'tag'], '')));
		Assert::false($this->client->isCacheable(new Http\Response(201, ['Last-Modified' => 'today'], '')));

		Assert::false($this->client->isCacheable(new Http\Response(200, ['Cache-Control' => 'max-age=0', 'ETag' => 'tag'], '')));
		Assert::false($this->client->isCacheable(new Http\Response(200, ['Cache-Control' => 'max-age=0', 'Last-Modified' => 'today'], '')));

		Assert::false($this->client->isCacheable(new Http\Response(200, ['Cache-Control' => 'Must-Revalidate', 'ETag' => 'tag'], '')));
		Assert::false($this->client->isCacheable(new Http\Response(200, ['Cache-Control' => 'Must-Revalidate', 'Last-Modified' => 'today'], '')));
	}

}

(new CachedClientTestCase)->run();
