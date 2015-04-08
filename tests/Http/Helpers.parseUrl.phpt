<?php

require __DIR__ . '/../bootstrap.php';

use Bitbang\Http\Helpers;
use Tester\Assert;


$cases = [
	'' => [
		'path' => '',
	],

	'http://' => [
		'path' => 'http://',
	],

	'http://hostname' => [
		'scheme' => 'http',
		'authority' => 'hostname',
		'host' => 'hostname',
	],

	'http://hostname.tld' => [
		'scheme' => 'http',
		'authority' => 'hostname.tld',
		'host' => 'hostname.tld',
	],

	'http://hostname.tld/' => [
		'scheme' => 'http',
		'authority' => 'hostname.tld',
		'host' => 'hostname.tld',
		'path' => '/',
	],

	'http://hostname.tld/path' => [
		'scheme' => 'http',
		'authority' => 'hostname.tld',
		'host' => 'hostname.tld',
		'path' => '/path',
	],

	'http://hostname.tld/path/' => [
		'scheme' => 'http',
		'authority' => 'hostname.tld',
		'host' => 'hostname.tld',
		'path' => '/path/',
	],

	'http://hostname.tld/path/file.ext' => [
		'scheme' => 'http',
		'authority' => 'hostname.tld',
		'host' => 'hostname.tld',
		'path' => '/path/file.ext',
	],

	'http://hostname.tld/path/file.ext?' => [
		'scheme' => 'http',
		'authority' => 'hostname.tld',
		'host' => 'hostname.tld',
		'path' => '/path/file.ext',
		'query' => '',
	],

	'http://hostname.tld/path/file.ext?a=b' => [
		'scheme' => 'http',
		'authority' => 'hostname.tld',
		'host' => 'hostname.tld',
		'path' => '/path/file.ext',
		'query' => 'a=b',
	],

	'http://hostname.tld/path/file.ext?a=b#' => [
		'scheme' => 'http',
		'authority' => 'hostname.tld',
		'host' => 'hostname.tld',
		'path' => '/path/file.ext',
		'query' => 'a=b',
		'fragment' => '',
	],

	'http://hostname.tld/path/file.ext?a=b#fragment' => [
		'scheme' => 'http',
		'authority' => 'hostname.tld',
		'host' => 'hostname.tld',
		'path' => '/path/file.ext',
		'query' => 'a=b',
		'fragment' => 'fragment',
	],

	'http://hostname.tld:8080/path/file.ext?a=b#fragment' => [
		'scheme' => 'http',
		'authority' => 'hostname.tld:8080',
		'host' => 'hostname.tld',
		'port' => '8080',
		'path' => '/path/file.ext',
		'query' => 'a=b',
		'fragment' => 'fragment',
	],

	'http://username@hostname.tld:8080/path/file.ext?a=b#fragment' => [
		'scheme' => 'http',
		'authority' => 'username@hostname.tld:8080',
		'user' => 'username',
		'host' => 'hostname.tld',
		'port' => '8080',
		'path' => '/path/file.ext',
		'query' => 'a=b',
		'fragment' => 'fragment',
	],

	'http://username:password@hostname.tld:8080/path/file.ext?a=b#fragment' => [
		'scheme' => 'http',
		'authority' => 'username:password@hostname.tld:8080',
		'user' => 'username',
		'pass' => 'password',
		'host' => 'hostname.tld',
		'port' => '8080',
		'path' => '/path/file.ext',
		'query' => 'a=b',
		'fragment' => 'fragment',
	],

	'HtTp://UsErNaMe:PaSsWoRd@HoStNaMe.TlD:8080/PaTh/FiLe.ExT?A=b#FrAgMeNt' => [
		'scheme' => 'HtTp',
		'authority' => 'UsErNaMe:PaSsWoRd@HoStNaMe.TlD:8080',
		'user' => 'UsErNaMe',
		'pass' => 'PaSsWoRd',
		'host' => 'HoStNaMe.TlD',
		'port' => '8080',
		'path' => '/PaTh/FiLe.ExT',
		'query' => 'A=b',
		'fragment' => 'FrAgMeNt',
	],

	'//hostname' => [
		'authority' => 'hostname',
		'host' => 'hostname',
	],

	'//hostname/' => [
		'authority' => 'hostname',
		'host' => 'hostname',
		'path' => '/',
	],

	'//hostname:8080' => [
		'authority' => 'hostname:8080',
		'host' => 'hostname',
		'port' => '8080',
	],

	'//hostname:8080/path' => [
		'authority' => 'hostname:8080',
		'host' => 'hostname',
		'port' => '8080',
		'path' => '/path',
	],

	'//hostname:8080/path:123' => [
		'authority' => 'hostname:8080',
		'host' => 'hostname',
		'port' => '8080',
		'path' => '/path:123',
	],

	'http://hostname#fragment/fragment' => [
		'scheme' => 'http',
		'authority' => 'hostname',
		'host' => 'hostname',
		'fragment' => 'fragment/fragment',
	],

	'http://hostname#fragment?fragment' => [
		'scheme' => 'http',
		'authority' => 'hostname',
		'host' => 'hostname',
		'fragment' => 'fragment?fragment',
	],

	'http://hostname#fragment#fragment' => [
		'scheme' => 'http',
		'authority' => 'hostname',
		'host' => 'hostname',
		'fragment' => 'fragment#fragment',
	],

	'http://hostname?query?query' => [
		'scheme' => 'http',
		'authority' => 'hostname',
		'host' => 'hostname',
		'query' => 'query?query',
	],

	'http://:@hostname' => [
		'scheme' => 'http',
		'authority' => ':@hostname',
		'user' => '',
		'pass' => '',
		'host' => 'hostname',
	],

	'http://username:@hostname' => [
		'scheme' => 'http',
		'authority' => 'username:@hostname',
		'user' => 'username',
		'pass' => '',
		'host' => 'hostname',
	],

	'http://:password@hostname' => [
		'scheme' => 'http',
		'authority' => ':password@hostname',
		'user' => '',
		'pass' => 'password',
		'host' => 'hostname',
	],

	'/path' => [
		'path' => '/path',
	],

	'path' => [
		'path' => 'path',
	],

	'?query' => [
		'query' => 'query',
	],

	'#fragment' => [
		'fragment' => 'fragment',
	],
];


foreach ($cases as $url => $parsed) {
	Assert::same($parsed, Helpers::parseUrl($url));
}
