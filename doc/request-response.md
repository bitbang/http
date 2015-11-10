# HTTP Request & Response

There are two classes. [Http\Request](https://github.com/bitbang/http/blob/master/src/Http/Request.php) and [Http\Response](https://github.com/bitbang/http/blob/master/src/Http/Response.php). They have few common methods encapsulated in their ancestor, the abstract [Http\Message](https://github.com/bitbang/http/blob/master/src/Http/Message.php) class.

You will usually instantiate the Request object. And the Response object you will usually get from the client as a response to request.


## Common methods to work with HTTP headers

Both Request and Response objects have the same methods for HTTP headers reading. Header names are always case-insensitive.

#### hasHeader($name)
Returns TRUE/FALSE when header exists.

#### hasMultiHeader($name)
Returns TRUE/FALSE when header exists and exists more then once. E.g `Set-Cookie`.

#### getHeader($name, $default = NULL)
Returns header value if exists, `$default` otherwise.

#### getMultiHeader($name, array $defaults = [])
Returns header values as an array if exists, `$defaults` otherwise. The result is always an array even the header value is only one.

#### getHeaders()
Returns all headers as an indexed array. Indexes are lower-cased header names. If is the header multi-value, the last one is returned.

#### getMultiHeaders()
Returns all headers as an indexed array of arrays. Indexes are lower-cased header names. Even simple header values are returns as an array.


## Working with payload

Both Request and Response objects have the same method `getBody()`. It returns raw body payload. It can be NULL when never set.


## Request

```php
use Bitbang\Http;

$request = new Http\Request(
    Http\Request::POST,            # HTTP request method
    'http://example.com',          # request URL
    ['X-Some-Header' => 'value'],  # HTTP headers (optional)
    '...raw payload...'            # payload (optional)
);
```

Among the headers methods mentioned above, the Request has more methods to adjust HTTP headers. And other methods specific to HTTP request.

#### addHeader($name, $value)
Sets header value only if header does not exist.

#### addMultiHeader($name, $value)
Adds another value to header. The value is always added even the same pair header-value already exists.

#### setHeader($name, $value)
Replaces header value. The header value is always overwritten. The `NULL` removes header.

#### setMultiHeader($name, array $value)
Replaces header with multi value. The header value is always overwritten. The empty array removes header.

#### isMethod($method)
Returns TRUE/FALSE when request HTTP method is `$method`. Comparison is case-insensitive, so result is same for `POST` or `PoSt`. You can find some class constants but list is not exhausting.

#### getMethod()
Returns request HTTP method as it has been set in constructor. When you set `DeLeTe` you will get `DeLeTe`.

#### getUrl()
Returns request URL as it has been set in constructor.


## Response

```php
use Bitbang\Http;

$request = new Http\Response(
    404,                           # HTTP status code
    ['X-Some-Header' => 'value'],  # HTTP headers
    '...raw payload...'            # payload
);
```

The Response class has no methods to set or append HTTP headers. By other words, headers are read only. If you need it, instantiate a new Response object and pass it by constructor.

Following methods are response specific.

#### getCode()
Returns HTTP response status code. Always as an integer. E.g. 200, 301, 404, 500... Some of them are available as class constants.

#### isCode($code)
Returns TRUE/FALSE when response status code is `$code`.

#### getPrevious()
Returns previous HTTP response if exists, NULL otherwise. This response chaining happens on HTTP redirection or response caching. So you can see whole HTTP communication.

#### setPrevious(Http\Response $response)
Sets previous HTTP response. You will get `Http\LogicException` when previous is already set. More or less, you should not use this method.


### Example - Working with headers
```php
use Bitbang\Http;

$request = new Http\Request('GET', 'http://example.com', ['X-My-Header' => "It's me there"]);

$request->addHeader('X-My-Header', 'Hello');  # does nothing, header already exists
$request->setHeader('X-My-Header', 'Hello');  # replaces header value
$request->setHeader('X-My-Header', NULL);     # removes header
$request->getHeader('X-My-Header', 'abcde');  # returns 'abcde',
                                              # because header has been removed before

$request->addHeader('User-Agent', 'bitbang/http');  # adds header, previous does not exist

$request->addMultiHeader('Set-Cookie', 'key1=value');  # adds header
$request->addMultiHeader('Set-Cookie', 'key2=value');  # adds header
$request->addMultiHeader('Set-Cookie', 'key3=value');  # adds header

$request->getHeaders();  # returns
[
    'user-agent' => 'bitbang/http',
    'set-cookie' => 'key3=value',
]

$request->getMultiHeaders();  # returns
[
    'user-agent' => [
        'bitbang/http',
    ],
    
    'set-cookie' => [
        'key1=value',
        'key2=value',
        'key3=value',
    ],
]
```
