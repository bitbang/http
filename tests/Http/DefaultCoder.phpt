<?php

require __DIR__ . '/../bootstrap.php';

use Bitbang\Http;
use Tester\Assert;


# Encode
test(function () {
	$coder = new Http\Coders\DefaultCoder;
	$request = new Http\Request('POST', 'url://', [], NULL, $coder);

	Assert::same([], $request->getMultiHeaders());
	Assert::same(NULL, $request->getBody());
});


test(function () {
	$coder = new Http\Coders\DefaultCoder;
	$request = new Http\Request('POST', 'url://', [], '', $coder);

	Assert::same([], $request->getMultiHeaders());
	Assert::same('', $request->getBody());
});


test(function () {
	$coder = new Http\Coders\DefaultCoder;
	$request = new Http\Request('POST', 'url://', [], 'string', $coder);

	Assert::same([], $request->getMultiHeaders());
	Assert::same('string', $request->getBody());
});


test(function () {
	$coder = new Http\Coders\DefaultCoder;
	$request = new Http\Request('POST', 'url://', [], [], $coder);

	Assert::same([
		'content-type' => ['application/x-www-form-urlencoded'],
		'content-length' => [0],
	], $request->getMultiHeaders());
	Assert::same('', $request->getBody());
});


test(function () {
	$coder = new Http\Coders\DefaultCoder;
	$request = new Http\Request('POST', 'url://', [], ['a' => 'b', 'c' => 'd'], $coder);

	Assert::same([
		'content-type' => ['application/x-www-form-urlencoded'],
		'content-length' => [7],
	], $request->getMultiHeaders());
	Assert::same('a=b&c=d', $request->getBody());
});


test(function () {
	$coder = new Http\Coders\DefaultCoder;
	$request = new Http\Request('POST', 'url://', ['Content-Type' => 'foo', 'Content-Length' => -1], [], $coder);

	Assert::same([
		'content-type' => ['application/x-www-form-urlencoded'],
		'content-length' => [0],
	], $request->getMultiHeaders());
	Assert::same('', $request->getBody());
});



# Decode
$coder = new Http\Coders\DefaultCoder;

Assert::null($coder->decode(new Http\Response(200, [], NULL)));
Assert::same('', $coder->decode(new Http\Response(200, [], '')));
Assert::same('string', $coder->decode(new Http\Response(200, [], 'string')));
