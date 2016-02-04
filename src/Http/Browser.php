<?php

namespace Bitbang\Http;


/**
 * HTTP client with requests factory.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class Browser
{
	use Strict;

	/** @var IDecoder */
	private $decoder;

	/** @var IClient */
	private $client;

	/** @var string */
	private $baseUrl;

	/** @var array */
	private $defaultHeaders;


	/**
	 * @param  string|NULL $baseUrl
	 * @param  array $defaultHeaders
	 * @param  IDecoder $decoder
	 * @param  IClient  $client
	 * @param  IDecoder
	 * @throws LogicException  when base URL is not absolute
	 */
	public function __construct($baseUrl = NULL, array $defaultHeaders = [], IDecoder $decoder = NULL, IClient $client = NULL)
	{
		if ($baseUrl !== NULL) {
			$baseUrl = (string) $baseUrl;
			if (!array_key_exists('scheme', Helpers::parseUrl($baseUrl))) {
				throw new LogicException("Base URL '$baseUrl' must be absolute.");
			}
		}

		$this->baseUrl = $baseUrl;
		$this->defaultHeaders = $defaultHeaders;
		$this->decoder = $decoder ?: new Decoders\DefaultDecoder;
		$this->client = $client ?: (extension_loaded('curl') ? new Clients\CurlClient : new Clients\StreamClient);
	}


	/**
	 * @return string|NULL
	 */
	public function getBaseUrl()
	{
		return $this->baseUrl;
	}


	/**
	 * @return array
	 */
	public function getDefaultHeaders()
	{
		return $this->defaultHeaders;
	}


	/**
	 * @return IDecoder
	 */
	public function getDecoder()
	{
		return $this->decoder;
	}


	/**
	 * @return IClient
	 */
	public function getClient()
	{
		return $this->client;
	}


	/**
	 * @param  string
	 * @param  array
	 * @return Response
	 */
	public function delete($url, array $headers = [])
	{
		return $this->process(
			$this->createRequest(Request::DELETE, $url, $headers)
		);
	}


	/**
	 * @param  string
	 * @param  array
	 * @return Response
	 */
	public function get($url, array $headers = [])
	{
		return $this->process(
			$this->createRequest(Request::GET, $url, $headers)
		);
	}


	/**
	 * @param  string
	 * @param  array
	 * @return Response
	 */
	public function head($url, array $headers = [])
	{
		return $this->process(
			$this->createRequest(Request::HEAD, $url, $headers)
		);
	}


	/**
	 * @param  string
	 * @param  string
	 * @param  array
	 * @return Response
	 */
	public function patch($url, $body, array $headers = [])
	{
		return $this->process(
				$this->createRequest(Request::PATCH, $url, $headers, (string) $body)
		);
	}


	/**
	 * @param  string
	 * @param  string
	 * @param  array
	 * @return Response
	 */
	public function post($url, $body, array $headers = [])
	{
		return $this->process(
				$this->createRequest(Request::POST, $url, $headers, (string) $body)
		);
	}


	/**
	 * @param  string
	 * @param  string
	 * @param  array
	 * @return Response
	 */
	public function put($url, $body = '', array $headers = [])
	{
		return $this->process(
				$this->createRequest(Request::PUT, $url, $headers, (string) $body)
		);
	}


	/**
	 * @param  string
	 * @param  string
	 * @param  array
	 * @param  string|NULL
	 * @return Request
	 */
	protected function createRequest($method, $url, array $headers, $body = NULL)
	{
		if ($this->baseUrl !== NULL) {
			$url = Helpers::absolutizeUrl($this->baseUrl, $url);
		}

		$request = new Request($method, $url, $headers, $body, $this->getDecoder());
		foreach ($this->defaultHeaders as $name => $value) {
			$request->addHeader($name, $value);
		}

		return $request;
	}


	/**
	 * @param  Request
	 * @return Response
	 */
	protected function process(Request $request)
	{
		return $this->client->process($request);
	}

}
