<?php

/** @testCase */

require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/inc/ClientsTestCase.php';

use Bitbang\Http;
use Bitbang\Http\Clients;


class StreamClientTestCase extends ClientsTestCase
{
	protected function getClient()
	{
		return new Clients\StreamClient;
	}

}

(new StreamClientTestCase(BASE_URL))->run();
