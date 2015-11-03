Introduction
============
This library is a quite low-level light-weight HTTP client. You send GET/POST/HEAD/DELETE/... request and you get simple HTTP response object. It works with `cURL` or with the `stream_get_contents()`.


Quick start
===========
```php
use Bitbang\Http;

$client = new Http\Clients\CurlClient;

$request = new Http\Request('GET', 'http://example.com', [
	'Accept' => 'text/plain',
	'User-Agent' => 'milo/http-client',
]);

$response = $client->process($request);

var_dump($response->getCode());
var_dump($response->getHeaders());
vat_dump($response->getBody());
```


Topics
======
- [Library class](library.md)
- Exceptions (TODO)
- [Working with Request & Response](request-response.md)
- Client implementations (TODO)
- Caching (TODO)
- Browser (TODO)
- Helpers (TODO)
