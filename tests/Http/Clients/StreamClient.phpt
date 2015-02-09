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
	protected function createClient()
	{
		return new Clients\StreamClient;
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
