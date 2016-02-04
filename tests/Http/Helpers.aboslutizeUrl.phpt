<?php

require __DIR__ . '/../bootstrap.php';

use Bitbang\Http\Helpers;
use Tester\Assert;


test(function() {
	$absolute = 'http://hostname.tld/path/file.ext?query#fragment';
	$cases = [
		'//host2' => 'http://host2',
		'/' => 'http://hostname.tld/',
		'file2' => 'http://hostname.tld/path/file2',
		'?query2' => 'http://hostname.tld/path/file.ext?query2',
		'#fragment2' => 'http://hostname.tld/path/file.ext?query#fragment2',
	];

	foreach ($cases as $relative => $result) {
		Assert::same($result, Helpers::absolutizeUrl($absolute, $relative));
	}
});


test(function() {
	$absolute = 'http://hostname.tld';
	$cases = [
		'//host2' => 'http://host2',
		'/' => 'http://hostname.tld/',
		'file2' => 'http://hostname.tld/file2',
		'?query2' => 'http://hostname.tld?query2',
		'#fragment2' => 'http://hostname.tld#fragment2',
	];

	foreach ($cases as $relative => $result) {
		Assert::same($result, Helpers::absolutizeUrl($absolute, $relative));
	}
});


test(function() {
	$absolute = 'http://hostname.tld/';
	$cases = [
		'//host2' => 'http://host2',
		'/' => 'http://hostname.tld/',
		'file2' => 'http://hostname.tld/file2',
		'?query2' => 'http://hostname.tld/?query2',
		'#fragment2' => 'http://hostname.tld/#fragment2',
	];

	foreach ($cases as $relative => $result) {
		Assert::same($result, Helpers::absolutizeUrl($absolute, $relative));
	}
});


test(function() {
	$absolute = 'http://hostname.tld/?query';
	$cases = [
		'//host2' => 'http://host2',
		'/' => 'http://hostname.tld/',
		'file2' => 'http://hostname.tld/file2',
		'?query2' => 'http://hostname.tld/?query2',
		'#fragment2' => 'http://hostname.tld/?query#fragment2',
	];

	foreach ($cases as $relative => $result) {
		Assert::same($result, Helpers::absolutizeUrl($absolute, $relative));
	}
});

Assert::same('http://hostname.tld/#fragment', Helpers::absolutizeUrl('http://hostname.tld/#fragment', ''));
