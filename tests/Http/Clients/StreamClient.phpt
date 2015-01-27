<?php

/** @testCase */

require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/inc/ClientsTestCase.php';

use Bitbang\Http\Clients;


class StreamClientTestCase extends ClientsTestCase
{
	protected function createClient()
	{
		return new Clients\StreamClient;
	}

}

(new StreamClientTestCase(getBaseUrl()))->run();
