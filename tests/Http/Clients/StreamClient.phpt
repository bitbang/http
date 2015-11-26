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
	protected function createClient($optionsOrCallback = NULL)
	{
		return new Clients\StreamClient($optionsOrCallback);
	}


	public function testClientSetupOptions()
	{
		$client = $this->createClient([
			'http' => [
				'method' => 'PUT',
			],
		]);

		$response = $client->process(
			new Request('GET', $this->baseUrl . '/method')
		);

		Assert::same('method-PUT', $response->getBody());
	}


	public function testClientSetupCallback()
	{
		$client = $this->createClient(function($context, $url) use (& $called) {
			Assert::type('resource', $context);
			Assert::same('stream-context', get_resource_type($context));
			Assert::match('%a%/ping', $url);

			$called = TRUE;
		});

		$client->process(
			new Request('GET', $this->baseUrl . '/ping')
		);

		Assert::true($called);
	}


	public function testUserAgent()
	{
		$response = $this->createClient()->process(
			new Request('GET', $this->baseUrl . '/user-agent')
		);

		Assert::same('Bitbang/' . Library::VERSION . ' (Stream)', $response->getHeader('X-User-Agent'));
	}

}

(new StreamClientTestCase(getBaseUrl()))->run();
