<?php

if (PHP_VERSION_ID < 50400) {
	throw new \LogicException("Bitbang HTTP library requires PHP 5.4.0 at least.");
}


require __DIR__ . '/Http/IClient.php';
require __DIR__ . '/Http/ICache.php';
require __DIR__ . '/Http/ICoder.php';

require __DIR__ . '/Http/exceptions.php';
require __DIR__ . '/Http/Helpers.php';
require __DIR__ . '/Http/Library.php';
require __DIR__ . '/Http/Strict.php';

require __DIR__ . '/Http/Clients/AbstractClient.php';
require __DIR__ . '/Http/Clients/CachedClient.php';
require __DIR__ . '/Http/Clients/CurlClient.php';
require __DIR__ . '/Http/Clients/StreamClient.php';

require __DIR__ . '/Http/Storages/FileCache.php';

require __DIR__ . '/Http/Coders/DefaultCoder.php';

require __DIR__ . '/Http/Message.php';
require __DIR__ . '/Http/Request.php';
require __DIR__ . '/Http/Response.php';

require __DIR__ . '/Http/Browser.php';
