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
$headers = [
	'a' => 'aaa',
	'A' => 'AAA',
	'B' => 'bbb'
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

Assert::same($message, $message->addHeader('Added', 'val'));
Assert::same('val', $message->getHeader('added'));

Assert::same($message, $message->addHeader('a', 'new-val'));
Assert::same('AAA', $message->getHeader('a'));

Assert::same($message, $message->setHeader('Set', 'val'));
Assert::same('val', $message->getHeader('Set'));

Assert::same($message, $message->setHeader('a', 'val'));
Assert::same('val', $message->getHeader('a'));

Assert::same($message, $message->setHeader('a', NULL));
Assert::false($message->hasHeader('a'));


# Content
Assert::same(NULL, (new TestMessage([], NULL))->getContent());
Assert::same('', (new TestMessage([], ''))->getContent());
