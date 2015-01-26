<?php

require __DIR__ . '/../bootstrap.php';

use Bitbang\Http;
use Tester\Assert;


$response = new Http\Response('200', [], '');

Assert::same(200, $response->getCode());
Assert::true($response->isCode(200));
Assert::true($response->isCode('200'));
Assert::false($response->isCode(0));


# Previous
$response = new Http\Response('200', [], '1');
$previous = new Http\Response('200', [], '2');
Assert::null($response->getPrevious());

$response->setPrevious($previous);
Assert::same($previous, $response->getPrevious());

Assert::exception(function() use ($response, $previous) {
	$response->setPrevious($previous);
}, 'Bitbang\Http\LogicException', 'Previous response is already set.');
