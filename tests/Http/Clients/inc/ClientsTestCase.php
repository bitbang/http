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


	/** @return Bitbang\Http\Clients\AbstractClient */
	abstract protected function createClient();


	public function test200()
	{
		$response = $this->createClient()->request(
			new Request('GET', $this->baseUrl . '/200')
		);

		Assert::same('The 200 response.', $response->getContent());
		Assert::same(200, $response->getCode());
	}


	public function test404()
	{
		$response = $this->createClient()->request(
			new Request('GET', $this->baseUrl . '/404')
		);

		Assert::same('The 404 response.', $response->getContent());
		Assert::same(404, $response->getCode());
	}


	public function testReceiveHeaders()
	{
		$response = $this->createClient()->request(
			new Request('GET', $this->baseUrl . '/receive-headers')
		);

		Assert::same('bitbang/http.tests', $response->getHeader('X-Powered-By'));
	}


	public function testSendHeaders()
	{
		$rand = rand(1, 999);

		$response = $this->createClient()->request(
			new Request('GET', $this->baseUrl . '/send-headers', ['X-Foo' => "foo-$rand"])
		);

		Assert::same("bar-foo-$rand", $response->getHeader('X-Bar'));
	}


	public function testRedirectAlways()
	{
		$client = $this->createClient();
		$client->redirectCodes = NULL;

		$response = $client->request(
			new Request('GET', $this->baseUrl . '/redirect/201')
		);

		Assert::same('Redirection finished', $response->getContent());
		Assert::same(200, $response->getCode());

		$previous = $response->getPrevious();
		Assert::type('Bitbang\Http\Response', $previous);
		Assert::same('Redirection made', $previous->getContent());
		Assert::same(201, $previous->getCode());
		Assert::null($previous->getPrevious());
	}


	public function testRedirectCodes()
	{
		$client = $this->createClient();
		$client->redirectCodes = [307];

		$response = $client->request(
			new Request('GET', $this->baseUrl . '/redirect/201')
		);

		Assert::same('Redirection made', $response->getContent());
		Assert::same(201, $response->getCode());
		Assert::true($response->hasHeader('Location'));
		Assert::null($response->getPrevious());


		$response = $client->request(
			new Request('GET', $this->baseUrl . '/redirect/307')
		);

		Assert::same('Redirection finished', $response->getContent());
		Assert::same(200, $response->getCode());

		$previous = $response->getPrevious();
		Assert::type('Bitbang\Http\Response', $previous);
		Assert::same('Redirection made', $previous->getContent());
		Assert::same(307, $previous->getCode());
		Assert::null($previous->getPrevious());
	}


	public function testRedirectNever()
	{
		$client = $this->createClient();
		$client->redirectCodes = [];

		$response = $client->request(
			new Request('GET', $this->baseUrl . '/redirect/201')
		);

		Assert::same('Redirection made', $response->getContent());
		Assert::same(201, $response->getCode());
		Assert::true($response->hasHeader('Location'));
		Assert::null($response->getPrevious());


		$response = $client->request(
			new Request('GET', $this->baseUrl . '/redirect/307')
		);

		Assert::same('Redirection made', $response->getContent());
		Assert::same(307, $response->getCode());
		Assert::true($response->hasHeader('Location'));
		Assert::null($response->getPrevious());
	}


	public function testOnRequestOnResponse()
	{
		$client = $this->createClient();

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
