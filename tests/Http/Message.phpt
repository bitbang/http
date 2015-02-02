<?php

require __DIR__ . '/../bootstrap.php';

use Bitbang\Http;
use Tester\Assert;


class TestMessage extends Http\Message
{
	public function addHeader($name, $value) { return parent::addHeader($name, $value); }
	public function setHeader($name, $value) { return parent::setHeader($name, $value); }
}


# Headers
test(function() {
	$headers = [
		'a' => 'aaa',
		'A' => 'AAA',
		'B' => 'bbb',
	];

	$message = new TestMessage($headers);
	Assert::same([
		'a' => 'AAA',
		'b' => 'bbb',
	], $message->getHeaders());

	Assert::true($message->hasHeader('a'));
	Assert::true($message->hasHeader('A'));
	Assert::false($message->hasHeader('foo'));

	Assert::null($message->getHeader('foo'));
	Assert::same('default', $message->getHeader('foo', 'default'));

	$message->addHeader('Added', 'val');
	Assert::same('val', $message->getHeader('added'));

	$message->addHeader('a', 'new-val');
	Assert::same('AAA', $message->getHeader('a'));

	$message->setHeader('Set', 'val');
	Assert::same('val', $message->getHeader('Set'));

	$message->setHeader('a', 'val');
	Assert::same('val', $message->getHeader('a'));

	$message->setHeader('a', NULL);
	Assert::false($message->hasHeader('a'));
});


# Content
test(function() {
	Assert::same(NULL, (new TestMessage([], NULL))->getContent());
	Assert::same('', (new TestMessage([], ''))->getContent());
});


# Fluent
test(function() {
	$message = new TestMessage;

	Assert::same($message, $message->addHeader('foo', 'bar'));
	Assert::same($message, $message->setHeader('foo', 'bar'));
});
