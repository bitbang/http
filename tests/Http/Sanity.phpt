<?php

require __DIR__ . '/../bootstrap.php';

use Bitbang\Http;
use Tester\Assert;


class C extends Http\Sanity
{}


Assert::exception(function() {
	(new C)->undefined;
}, 'Bitbang\Http\LogicException', 'Cannot read an undeclared property C::$undefined.');

Assert::exception(function() {
	$o  = new C;
	$o->undefined = '';
}, 'Bitbang\Http\LogicException', 'Cannot write to an undeclared property C::$undefined.');
