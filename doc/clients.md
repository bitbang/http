# Client implementations

There are two (three) HTTP client implementations. The [Http\Clients\StreamClient](https://github.com/bitbang/http/blob/master/src/Http/Clients/StreamClient.php) and the [Http\Clients\CurlClients](https://github.com/bitbang/http/blob/master/src/Http/Clients/CurlClient.php). They implement the [Http\IClient](https://github.com/bitbang/http/blob/master/src/Http/IClient.php) interface which looks like:

```php
namespace Bitbang\Http;

interface IClient
{
	/** @return Response */
	function request(Request $request);
	
	function onRequest($callback);

	function onResponse($callback);
}
```

The `request()` method is the most important, the `onRequest()` and `onResponse()` are for traffic observation.


## StreamClient

This client uses `file_get_contents()` function with HTTP context. By constructor, you can pass [SSL Context options](http://php.net/manual/en/context.ssl.php) or callback.

```php
use Bitbang\Http;

# Without parameters
$client = new Http\Clients\StreamClient;

# SSL Context options
$client = new Http\Clients\StreamClient([
	'cafile' => '/etc/ssl/trusted.ca.pem',
]);

# Callback for fine adjustment
# It is called before every HTTP request (before stream_get_contents() to be exact)
$client = new Http\Clients\StreamClient(function (resource $context, string $url) {
	stream_context_set_option($context, [...]);
});
```


## CurlClient

This client uses `cURL` functions, so the PHP cURL extension has to be loaded. I recommend this client, because it uses HTTP keep-alive connection. By constructor, you can pass [cURL options](http://php.net/manual/en/function.curl-setopt.php) or callback.

```php
use Bitbang\Http;

# Without parameters
$client = new Http\Clients\CurlClient;

# SSL Context options
$client = new Http\Clients\CurlClient([
	CURLOPT_CAINFO => '/path/to/trusted-ca.pem',
	CURLOPT_CONNECTTIMEOUT => 2,
]);

# Callback for fine adjustment
# It is called before every HTTP request (before curl_exec() to be exact)
$client = new Http\Clients\CurlClient(function (resource $curl, string $url) {
	curl_setopt_array($curl, [...]);
});
```


## CachedClient

It is not real client. It is a client wrapper with caching capability. The caching strategy is now simple (and not perfect):

1. calculate the request finger print
2. if exists response for such request in cache and has `Last-Modified` or `ETag` header, add those header to request by `If-Modified-Since` or `If-None-Match`
3. perform HTTP request
4. web server may return HTTP status 304 - NOT MODIFIED
5. if so, return response from cache (and this saves traffic)

The imperfection is the request finger print calculation. It should be calculated from all headers mentioned by `Vary` header, but it is calculated only from `Accept`, `Accept-Encoding` and `Authorization` headers now. Well, I'm using it and it works. If you need, open the issue please.

```php
use Bitbang\Http;

$cache = new Http\Storages\FileCache('/tmp');  # this is naive Http\ICache implementation
$inner = new Http\Clients\CurlClient;

$client = new Http\Clients\CachedClient($cache, $client);
```

The cache storage has to implement [Http\ICache](https://github.com/bitbang/http/blob/master/src/Http/ICache.php) interface. The naive implementation [Http\Storages\FileCache](https://github.com/bitbang/http/blob/master/src/Http/Storages/FileCache.php) does not implement cache invalidation.


## Redirection (following the Location)

Mentioned client implementations disable internal HTTP redirection (`CURLOPT_FOLLOWLOCATION = FALSE` for CurlClient, `follow_location = 0` for StreamClient) and solves it by theirs own. The implementation is done in their parent, the [Http\Clients\AbstractClient](https://github.com/bitbang/http/blob/master/src/Http/Clients/AbstractClient.php). You can control redirection by two public properties:

```php
use Bitbang\Http;

$client = new Http\Clients\...;

# How many times can client follow Location (default is 20)
$client->maxRedirects = 50;

# On which HTTP response code can client redirect
$client->redirectCodes = NULL;        # always (default)
$client->redirectCodes = [];          # never
$client->redirectCodes = [301, 308];  # only for these
```

A result of this implementation is, that you always get all requests and response in observation callbacks (read below).


## onRequest() & onResponse()

You can observe HTTP traffic by these methods. Usage is:
```php
use Bitbang\Http;

$client = new Http\Clients\...;
$client->onRequest(function (Http\Request $request) {
	var_dump($request);
});
$client->onResponse(function (Http\Response $response) {
	var_dump($response);
});

Note: Never modify `$request` or `$response` here. It doesn't worth it.
```


### Example - Download some HTML
```php
use Bitbang\Http;

$request = new Http\Request('GET', 'http://example.com');
$client = new Http\Clients\CurlClient;

$response = $client->process($request);

var_dump($response->getBody());
```


### Example - REST API request

TODO: show some meaningful code and delete the crap above
