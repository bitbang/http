<?php

require __DIR__ . '/../../bootstrap.php';

use Bitbang\Http\Clients;
use Bitbang\Http\Request;
use Tester\Assert;


if (!extension_loaded('openssl')) {
	Tester\Environment::skip('Test requires the OpenSSL extension.');
}


# Untrusted SSL CA
test(function() {
	$e = Assert::exception(function() {
		$client = new Clients\StreamClient(function($context) {
			stream_context_set_option($context, 'ssl', 'ciphers', 'ALL');  # testing SSL wrapper limitation
		});

		$client->process(
			new Request('GET', getBaseSslUrl())
		);
	}, 'Bitbang\Http\BadResponseException');

	$e = Assert::exception(function() use ($e) {
		throw $e->getPrevious();
	}, 'ErrorException', '%a%ailed to open stream%a%');

	$e = Assert::exception(function() use ($e) {
		throw $e->getPrevious();
	}, 'ErrorException', 'file_get_contents(): Failed to enable crypto');

	$e = Assert::exception(function() use ($e) {
		throw $e->getPrevious();
	}, 'ErrorException', '%A%certificate verify failed');

	Assert::null($e->getPrevious());
});


# Trusted SSL CA
test(function() {
	$client = new Clients\StreamClient(function($context) {
		stream_context_set_option($context, 'ssl', 'ciphers', 'ALL');  # testing SSL wrapper limitation
		stream_context_set_option($context, 'ssl', 'cafile', __DIR__ . '/../../server/cert/ca.pem');
	});

	$response = $client->process(
		new Request('GET', getBaseSslUrl() . '/ping')
	);

	Assert::type('Bitbang\\Http\\Response', $response);
	Assert::same('pong', $response->getBody());
});
