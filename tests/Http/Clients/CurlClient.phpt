<?php

/** @testCase */

require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/inc/ClientsTestCase.php';

if (!extension_loaded('curl')) {
	Tester\Environment::skip('The cURL extension is required for the test.');
}

use Bitbang\Http\Clients;
use Bitbang\Http\Library;
use Bitbang\Http\Request;
use Tester\Assert;


class CurlClientTestCase extends ClientsTestCase
{
	protected function createClient($optionsOrCallback = NULL)
	{
		return new Clients\CurlClient($optionsOrCallback);
	}


	public function testClientSetupOptions()
	{
		$client = $this->createClient([
			CURLOPT_CUSTOMREQUEST => 'PUT',
		]);

		$response = $client->request(
			new Request('GET', $this->baseUrl . '/method')
		);

		Assert::same('method-PUT', $response->getBody());
	}


	public function testClientSetupCallback()
	{
		$client = $this->createClient(function($context, $url) use (& $called) {
			Assert::type('resource', $context);
			Assert::same('curl', get_resource_type($context));
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

		Assert::same('Bitbang/' . Library::VERSION . ' (cUrl)', $response->getHeader('X-User-Agent'));
	}

}

(new CurlClientTestCase(getBaseUrl()))->run();
