<?php

require __DIR__ . '/../bootstrap.php';

use Bitbang\Http;
use Tester\Assert;


class TestMessage extends Http\Message
{
	public function addHeader($name, $value) { return parent::addHeader($name, $value); }
	public function addMultiHeader($name, $value, $append = TRUE) { return parent::addMultiHeader($name, $value, $append); }
	public function setHeader($name, $value) { return parent::setHeader($name, $value); }
	public function setMultiHeader($name, array $value) { return parent::setMultiHeader($name, $value); }
}


# Headers
test(function() {
	$headers = [
		'a' => 'aaa',
		'A' => 'AAA',
		'B' => 'bbb',
		'c' => NULL,
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


# Multi-headers
test(function() {
	$headers = [
		'a' => ['aaa', 'aaaa'],
		'A' => ['AAA', 'AAAA'],
		'B' => 'bbb',
		'c' => NULL,
		'd' => [],
		'e' => ['foo' => 'eee', 5 => 'eeee']
	];

	$message = new TestMessage($headers);
	Assert::same([
		'a' => ['AAA', 'AAAA'],
		'b' => ['bbb'],
		'e' => ['eee', 'eeee'],
	], $message->getMultiHeaders());

	Assert::same([
		'a' => 'AAAA',
		'b' => 'bbb',
		'e' => 'eeee',
	], $message->getHeaders());

	Assert::true($message->hasMultiHeader('a'));
	Assert::true($message->hasMultiHeader('A'));
	Assert::false($message->hasMultiHeader('b'));
	Assert::false($message->hasMultiHeader('foo'));

	Assert::same([], $message->getMultiHeader('foo'));
	Assert::same(['default', 'value'], $message->getMultiHeader('foo', ['default', 'value']));
	Assert::same('AAAA', $message->getHeader('a'));

	Assert::null($message->getHeader('Added'));
	Assert::same([], $message->getMultiHeader('Added'));

	$message->addMultiHeader('Added', 'm1');
	Assert::same(['m1'], $message->getMultiHeader('added'));

	$message->addMultiHeader('Added', 'm2');
	Assert::same(['m1', 'm2'], $message->getMultiHeader('added'));

	$message->addMultiHeader('Added', []);
	Assert::same(['m1', 'm2'], $message->getMultiHeader('added'));

	$message->addMultiHeader('Added', ['foo' => 'm4', 'bar' => 'm5']);
	Assert::same(['m1', 'm2', 'm4', 'm5'], $message->getMultiHeader('added'));
	Assert::same('m5', $message->getHeader('added'));

	$message->setMultiHeader('Set', ['val']);
	Assert::same(['val'], $message->getMultiHeader('Set'));

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
	Assert::same($message, $message->setMultiHeader('foo', ['bar']));
});
