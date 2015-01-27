<?php

require __DIR__ . '/../../bootstrap.php';

use Bitbang\Http\Storages;
use Tester\Assert;


$e = Assert::exception(function() {
	new Storages\FileCache(__DIR__ . DIRECTORY_SEPARATOR . 'non-exists');
}, 'Bitbang\Http\Storages\MissingDirectoryException', "Directory '%a%non-exists' is missing.");

Assert::null($e->getPrevious());


$cache = new Storages\FileCache(getTempDir());

Assert::null($cache->load('undefined'));

$value = $cache->save('key-1', NULL);
Assert::null($cache->load('key-1'));

$value = $cache->save('key-2', TRUE);
Assert::true($cache->load('key-2'));

$value = $cache->save('key-3', FALSE);
Assert::false($cache->load('key-3'));

$value = $cache->save('key-4', []);
Assert::same([], $cache->load('key-4'));

$value = $cache->save('key-5', [0, 'a', []]);
Assert::same([0, 'a', []], $cache->load('key-5'));

$value = $cache->save('key-6', new stdClass);
Assert::equal(new stdClass, $cache->load('key-6'));


Assert::exception(function() {
	mkdir($dir = getTempDir() . DIRECTORY_SEPARATOR . 'sub');
	file_put_contents($dir . DIRECTORY_SEPARATOR . Storages\FileCache::DIRECTORY, '');
	new Storages\FileCache($dir);
}, 'Bitbang\Http\Storages\MissingDirectoryException', "Cannot create '%a%' directory.");
