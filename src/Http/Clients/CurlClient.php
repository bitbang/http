<?php

namespace Bitbang\Http\Clients;

use Bitbang\Http;


/**
 * HTTP client which use the cURL extension functions.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class CurlClient extends AbstractClient
{
	/** @var callable|NULL */
	private $beforeCurlExec;

	/** @var resource */
	private $curl;


	/**
	 * @param  callable  function(resource $curlHandle, string url)
	 *
	 * @throws Http\LogicException
	 */
	public function __construct($beforeCurlExec = NULL)
	{
		if (!extension_loaded('curl')) {
			throw new Http\LogicException('cURL extension is not loaded.');
		}

		$this->beforeCurlExec = $beforeCurlExec;
	}


	protected function setupRequest(Http\Request $request)
	{
		parent::setupRequest($request);
		$request->addHeader('Connection', 'keep-alive');
		$request->addHeader('User-Agent', 'Bitbang/' . Http\Library::VERSION . ' (cUrl)');
	}


	/**
	 * @return Http\Response
	 *
	 * @throws Http\BadResponseException
	 */
	protected function process(Http\Request $request)
	{
		$headers = [];
		foreach ($request->getHeaders() as $name => $value) {
			$headers[] = "$name: $value";
		}

		$responseHeaders = [];

		$options = [
			CURLOPT_FOLLOWLOCATION => FALSE,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $request->getMethod(),
			CURLOPT_NOBODY => $request->isMethod(Http\Request::HEAD),
			CURLOPT_URL => $request->getUrl(),
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_POSTFIELDS => $request->getContent(),
			CURLOPT_HEADER => FALSE,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_SSL_VERIFYPEER => 1,
			CURLOPT_HEADERFUNCTION => function($curl, $line) use (& $responseHeaders, & $last) {
				if (strncasecmp($line, 'HTTP/', 5) === 0) {
					/** @todo Set proxy response as Response::setPrevious($proxyResponse)? */
					# The HTTP/x.y may occur multiple times with proxy (HTTP/1.1 200 Connection Established)
					$responseHeaders = [];

				} elseif (in_array(substr($line, 0, 1), [' ', "\t"], TRUE)) {
					$responseHeaders[$last] .= ' ' . trim($line);  # RFC2616, 2.2

				} elseif ($line !== "\r\n") {
					list($name, $value) = explode(':', $line, 2);
					$responseHeaders[$last = trim($name)] = trim($value);
				}

				return strlen($line);
			},
		];

		if (defined('CURLOPT_PROTOCOLS')) {  # HHVM issue. Even cURL v7.26.0, constants are missing.
			$options[CURLOPT_PROTOCOLS] = CURLPROTO_HTTP | CURLPROTO_HTTPS;
		}

		if (!$this->curl) {
			$this->curl = curl_init();
			if ($this->curl === FALSE) {
				throw new Http\BadResponseException('Cannot init cURL handler.');
			}
		}

		$result = curl_setopt_array($this->curl, $options);
		if ($result === FALSE) {
			throw new Http\BadResponseException('Setting cURL options failed: ' . curl_error($this->curl), curl_errno($this->curl));
		}

		$this->beforeCurlExec && call_user_func($this->beforeCurlExec, $this->curl, $request->getUrl());

		$content = curl_exec($this->curl);
		if ($content === FALSE) {
			throw new Http\BadResponseException(curl_error($this->curl), curl_errno($this->curl));
		}

		$code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		if ($code === FALSE) {
			throw new Http\BadResponseException('HTTP status code is missing: ' . curl_error($this->curl), curl_errno($this->curl));
		}

		return new Http\Response($code, $responseHeaders, $content);
	}

}
