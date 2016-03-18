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


	final public function test200()
	{
		$response = $this->createClient()->process(
			new Request('GET', $this->baseUrl . '/200')
		);

		Assert::same('The 200 response.', $response->getBody());
		Assert::same(200, $response->getCode());
	}


	final public function test404()
	{
		$response = $this->createClient()->process(
			new Request('GET', $this->baseUrl . '/404')
		);

		Assert::same('The 404 response.', $response->getBody());
		Assert::same(404, $response->getCode());
	}


	final public function testReceiveHeaders()
	{
		$response = $this->createClient()->process(
			new Request('GET', $this->baseUrl . '/receive-headers')
		);

		Assert::same(['bitbang/http.tests'], $response->getMultiHeader('X-Powered-By'));
		Assert::same(['one', 'two'], $response->getMultiHeader('X-Multi'));
	}


	final public function testSendHeaders()
	{
		$rand = rand(1, 999);

		$response = $this->createClient()->process(
			new Request('GET', $this->baseUrl . '/send-headers', ['X-Foo' => "foo-$rand"])
		);

		Assert::same("bar-foo-$rand", $response->getHeader('X-Bar'));
	}


//	final public function testReceiveMultipleLineHeader()
//	{
//		$response = $this->createClient()->request(
//			new Request('GET', $this->baseUrl . '/receive-multiple-line-header')
//		);
//
//		Assert::same('a b c', $response->getHeader('X-Bar'));
//	}


	final public function testRedirectAlways()
	{
		$client = $this->createClient();
		$client->redirectCodes = NULL;

		$response = $client->process(
			new Request('GET', $this->baseUrl . '/redirect/201')
		);

		Assert::same('Redirection finished', $response->getBody());
		Assert::same(200, $response->getCode());

		$previous = $response->getPrevious();
		Assert::type('Bitbang\Http\Response', $previous);
		Assert::same('Redirection made', $previous->getBody());
		Assert::same(201, $previous->getCode());
		Assert::null($previous->getPrevious());
	}


	final public function testRedirectCodes()
	{
		$client = $this->createClient();
		$client->redirectCodes = [307];

		$response = $client->process(
			new Request('GET', $this->baseUrl . '/redirect/201')
		);

		Assert::same('Redirection made', $response->getBody());
		Assert::same(201, $response->getCode());
		Assert::true($response->hasHeader('Location'));
		Assert::null($response->getPrevious());


		$response = $client->process(
			new Request('GET', $this->baseUrl . '/redirect/307')
		);

		Assert::same('Redirection finished', $response->getBody());
		Assert::same(200, $response->getCode());

		$previous = $response->getPrevious();
		Assert::type('Bitbang\Http\Response', $previous);
		Assert::same('Redirection made', $previous->getBody());
		Assert::same(307, $previous->getCode());
		Assert::null($previous->getPrevious());
	}


	final public function testRedirectNever()
	{
		$client = $this->createClient();
		$client->redirectCodes = [];

		$response = $client->process(
			new Request('GET', $this->baseUrl . '/redirect/201')
		);

		Assert::same('Redirection made', $response->getBody());
		Assert::same(201, $response->getCode());
		Assert::true($response->hasHeader('Location'));
		Assert::null($response->getPrevious());


		$response = $client->process(
			new Request('GET', $this->baseUrl . '/redirect/307')
		);

		Assert::same('Redirection made', $response->getBody());
		Assert::same(307, $response->getCode());
		Assert::true($response->hasHeader('Location'));
		Assert::null($response->getPrevious());
	}


	final public function testRelativeRedirect()
	{
		$client = $this->createClient();

		$response = $client->process(
			new Request('GET', $this->baseUrl . '/relative-redirect')
		);

		Assert::same('Redirection finished', $response->getBody());
		Assert::same(200, $response->getCode());

		$previous = $response->getPrevious();
		Assert::type('Bitbang\Http\Response', $previous);
		Assert::same('Redirection made', $previous->getBody());
		Assert::same(301, $previous->getCode());
		Assert::true($previous->hasHeader('Location'));
		Assert::same('/redirected', $previous->getHeader('Location'));
		Assert::null($previous->getPrevious());
	}


	final public function testOnRequestOnResponse()
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

		$client->process(
			new Request('GET', $this->baseUrl . '/ping')
		);

		Assert::type('Bitbang\Http\Request', $insideRequest);
		Assert::type('Bitbang\Http\Response', $insideResponse);
	}


	final public function testMaxRedirects()
	{
		$client = $this->createClient();
		$client->onRequest(function() use (& $counter) {
			$counter++;
		});

		$request = new Request('GET', $this->baseUrl . '/redirect-loop', ['X-Max-Loop-Count' => 5]);

		$client->maxRedirects = 6;
		$counter = -1;
		$response = $client->process(clone $request);
		Assert::same(5, $counter);
		Assert::same('Redirection finished', $response->getBody());
		Assert::same(200, $response->getCode());


		$client->maxRedirects = 5;
		$counter = -1;
		$response = $client->process(clone $request);
		Assert::same(5, $counter);
		Assert::same('Redirection finished', $response->getBody());
		Assert::same(200, $response->getCode());


		$client->maxRedirects = 4;
		$counter = -1;
		Assert::exception(function() use ($client, $request) {
			$client->process($request);
		}, 'Bitbang\Http\RedirectLoopException', 'Maximum redirect count (4) achieved.');
		Assert::same(4, $counter);
	}


	final public function testOwnUserAgent()
	{
		$response = $this->createClient()->process(
			new Request('GET', $this->baseUrl . '/user-agent', ['User-Agent' => 'Tested'])
		);

		Assert::same('Tested', $response->getHeader('X-User-Agent'));
	}


	final public function testHttpMethod()
	{
		$response = $this->createClient()->process(
			new Request('POST', $this->baseUrl . '/method')
		);
		Assert::same('method-POST', $response->getBody());

		$response = $this->createClient()->process(
			new Request('PUT', $this->baseUrl . '/method')
		);
		Assert::same('method-PUT', $response->getBody());

		$response = $this->createClient()->process(
			new Request('DELETE', $this->baseUrl . '/method')
		);
		Assert::same('method-DELETE', $response->getBody());

		$response = $this->createClient()->process(
			new Request('HEAD', $this->baseUrl . '/method')
		);
		Assert::same('', $response->getBody());
	}


	final public function testcoderPassing()
	{
		$coder = new Bitbang\Http\Coders\DefaultCoder;

		$response = $this->createClient()->process(
			new Request('GET', $this->baseUrl . '/200', [], NULL, $coder)
		);

		Assert::same($coder, $response->getCoder());
	}

}
