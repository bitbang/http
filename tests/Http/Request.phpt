<?php

require __DIR__ . '/../bootstrap.php';

use Bitbang\Http;
use Tester\Assert;


$request = new Http\Request('Foo', 'http://');

Assert::same('Foo', $request->getMethod());
Assert::true($request->isMethod('Foo'));
Assert::true($request->isMethod('FOO'));

Assert::same('http://', $request->getUrl());

Assert::same($request, $request->addHeader('foo', 'bar'));
Assert::same($request, $request->addMultiHeader('foo', ['bar']));
Assert::same($request, $request->setHeader('foo', 'bar'));
Assert::same($request, $request->setMultiHeader('foo', ['bar']));
