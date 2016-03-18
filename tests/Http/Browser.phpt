<?php

/**
 * @testCase
 */

require __DIR__ . '/../bootstrap.php';

use Bitbang\Http;
use Tester\Assert;


class MockBrowser extends Http\Browser
{
	protected function process(Http\Request $request)
	{
		return $request;  # loop-back
	}
}

class MockCoder implements Http\ICoder
{
	function decode(Http\Response $response) {}
}

class MockClient implements Http\IClient
{
	function process(Http\Request $request)
	{
		return new Http\Response(0, $request->getHeaders(), $request->getUrl(), $request->getCoder());
	}

	function onRequest($callback) {}
	function onResponse($callback) {}
}


class BrowserTestCase extends Tester\TestCase
{

	public function testGetters()
	{
		$browser = new Http\Browser(
			'http://hostname.tld',
			['Default' => 'Header'],
			$coder = new MockCoder,
			$client = new MockClient
		);

		Assert::same('http://hostname.tld', $browser->getBaseUrl());
		Assert::same(['Default' => 'Header'], $browser->getDefaultHeaders());
		Assert::same($coder, $browser->getCoder());
		Assert::same($client, $browser->getClient());
	}


	public function testExceptions()
	{
		Assert::exception(function () {
			new Http\Browser('/relative');
		}, 'Bitbang\Http\LogicException', "Base URL '/relative' must be absolute.");
	}


	public function testCreateRequest()
	{
		$browser = new Http\Browser('http://absolute/', ['Default' => 'Header', 'A' => 'B'], NULL, new MockClient);
		$response = $browser->get('?query', ['A' => 'C']);
		Assert::same('http://absolute/?query', $response->getBody());
		Assert::same(['a' => 'C', 'default' => 'Header'], $response->getHeaders());
	}


	public function testRequestFactories()
	{
		$browser = new MockBrowser;

		$this->assertRequest(
			$browser->delete('url://', ['H' => 'V']),
			'DELETE',
			NULL
		);

		$this->assertRequest(
			$browser->get('url://', ['H' => 'V']),
			'GET',
			NULL
		);

		$this->assertRequest(
			$browser->head('url://', ['H' => 'V']),
			'HEAD',
			NULL
		);

		$this->assertRequest(
			$browser->patch('url://', 'BoDy', ['H' => 'V']),
			'PATCH'
		);

		$this->assertRequest(
			$browser->post('url://', 'BoDy', ['H' => 'V']),
			'POST'
		);

		$this->assertRequest(
			$browser->put('url://', 'BoDy', ['H' => 'V']),
			'PUT'
		);
	}


	private function assertRequest($request, $method, $body = 'BoDy')
	{
		/** @var Http\Request $request */
		Assert::type('Bitbang\Http\Request', $request);
		Assert::same('url://', $request->getUrl());
		Assert::same(['h' => 'V'], $request->getHeaders());
		Assert::same($method, $request->getMethod());
		Assert::same($body, $request->getBody());
	}

}


(new BrowserTestCase)->run();
