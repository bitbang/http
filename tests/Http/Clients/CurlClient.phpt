<?php

/** @testCase */

require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/inc/ClientsTestCase.php';

if (!extension_loaded('curl')) {
	Tester\Environment::skip('The cURL extension is required for the test.');
}

use Bitbang\Http\Clients;


class CurlClientTestCase extends ClientsTestCase
{
	protected function getClient()
	{
		return new Clients\CurlClient;
	}

}

(new CurlClientTestCase(getBaseUrl()))->run();
