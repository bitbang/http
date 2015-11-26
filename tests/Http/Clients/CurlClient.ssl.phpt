<?php

require __DIR__ . '/../../bootstrap.php';

use Bitbang\Http\Clients;
use Bitbang\Http\Request;
use Tester\Assert;


if (!extension_loaded('curl')) {
	Tester\Environment::skip('The cURL extension is required for the test.');
}


# Untrusted SSL CA
test(function() {
	$e = Assert::exception(function() {
		$client = new Clients\CurlClient;
		$client->process(
			new Request('GET', getBaseSslUrl())
		);
	}, 'Bitbang\Http\BadResponseException', '%A%certificate%A%');

	Assert::null($e->getPrevious());
});


# Trusted SSL CA
test(function() {
	$client = new Clients\CurlClient(function($curl) {
		curl_setopt($curl, CURLOPT_CAINFO, __DIR__ . '/../../server/cert/ca.pem');
	});

	$response = $client->process(
		new Request('GET', getBaseSslUrl() . '/ping')
	);

	Assert::type('Bitbang\\Http\\Response', $response);
	Assert::same('pong', $response->getBody());
});
