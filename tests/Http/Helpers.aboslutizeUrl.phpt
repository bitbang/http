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
		'#fragment' => 'http://hostname.tld/path/file.ext?query#fragment',
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
		'#fragment' => 'http://hostname.tld#fragment',
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
		'#fragment' => 'http://hostname.tld/#fragment',
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
		'#fragment' => 'http://hostname.tld/?query#fragment',
	];

	foreach ($cases as $relative => $result) {
		Assert::same($result, Helpers::absolutizeUrl($absolute, $relative));
	}
});
