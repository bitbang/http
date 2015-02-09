<?php

/** @testCase */

require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/inc/ClientsTestCase.php';

use Bitbang\Http\Clients;
use Bitbang\Http\Library;
use Bitbang\Http\Request;
use Tester\Assert;


class StreamClientTestCase extends ClientsTestCase
{
	protected function createClient($onContextCreate = NULL)
	{
		return new Clients\StreamClient($onContextCreate);
	}


	public function testClientSetup()
	{
		$client = $this->createClient(function($context, $url) use (& $called) {
			Assert::type('resource', $context);
			Assert::same('stream-context', get_resource_type($context));
			Assert::match('%a%/ping', $url);

			$called = TRUE;
		});

		$client->request(
			new Request('GET', $this->baseUrl . '/ping')
		);

		Assert::true($called);
	}


	public function testUserAgent()
	{
		$response = $this->createClient()->request(
			new Request('GET', $this->baseUrl . '/user-agent')
		);

		Assert::same('Bitbang/' . Library::VERSION . ' (Stream)', $response->getHeader('X-User-Agent'));
	}

}

(new StreamClientTestCase(getBaseUrl()))->run();
