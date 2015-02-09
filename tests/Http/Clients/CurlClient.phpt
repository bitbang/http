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
	protected function createClient()
	{
		return new Clients\CurlClient;
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
