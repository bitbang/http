<?php

use Bitbang\Http\Request;
use Bitbang\Http\Response;
use Tester\Assert;


abstract class ClientsTestCase extends Tester\TestCase
{
	/** @var string */
	protected $baseUrl;


	/** @param string */
	public function __construct($baseUrl)
	{
		$this->baseUrl = $baseUrl;
	}


	/** @return Bitbang\Http\IClient */
	abstract protected function getClient();


	public function test200()
	{
		$response = $this->getClient()->request(
			new Request('GET', $this->baseUrl . '/200')
		);

		Assert::same('The 200 response.', $response->getContent());
		Assert::same(200, $response->getCode());
	}


	public function test404()
	{
		$response = $this->getClient()->request(
			new Request('GET', $this->baseUrl . '/404')
		);

		Assert::same('The 404 response.', $response->getContent());
		Assert::same(404, $response->getCode());
	}


	public function testReceiveHeaders()
	{
		$response = $this->getClient()->request(
			new Request('GET', $this->baseUrl . '/receive-headers')
		);

		Assert::same('bitbang/http.tests', $response->getHeader('X-Powered-By'));
	}


	public function testSendHeaders()
	{
		$rand = rand(1, 999);

		$response = $this->getClient()->request(
			new Request('GET', $this->baseUrl . '/send-headers', ['X-Foo' => "foo-$rand"])
		);

		Assert::same("bar-foo-$rand", $response->getHeader('X-Bar'));
	}


	public function testRedirection()
	{
		$response = $this->getClient()->request(
			new Request('GET', $this->baseUrl . '/redirect')
		);

		Assert::same('Redirection finished', $response->getContent());
		Assert::same(200, $response->getCode());

		$previous = $response->getPrevious();
		Assert::type('Bitbang\Http\Response', $previous);
		Assert::same('Redirection made', $previous->getContent());
		Assert::same(307, $previous->getCode());

		Assert::null($previous->getPrevious());
	}


	public function testOnRequestOnResponse()
	{
		$client = $this->getClient();

		$insideRequest = NULL;
		$client->onRequest(function(Request $request) use (& $insideRequest) {
			$insideRequest = $request;
		});

		$insideResponse = NULL;
		$client->onResponse(function(Response $response) use (& $insideResponse) {
			$insideResponse = $response;
		});

		Assert::null($insideRequest);
		Assert::null($insideResponse);

		$client->request(
			new Request('GET', $this->baseUrl . '/ping')
		);

		Assert::type('Bitbang\Http\Request', $insideRequest);
		Assert::type('Bitbang\Http\Response', $insideResponse);
	}

}
