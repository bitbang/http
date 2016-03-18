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

	/** @var ICoder */
	private $coder;

	/** @var IClient */
	private $client;

	/** @var string */
	private $baseUrl;

	/** @var array */
	private $defaultHeaders;


	/**
	 * @param  string|NULL $baseUrl
	 * @param  array $defaultHeaders
	 * @param  ICoder $coder
	 * @param  IClient  $client
	 * @param  ICoder
	 * @throws LogicException  when base URL is not absolute
	 */
	public function __construct($baseUrl = NULL, array $defaultHeaders = [], ICoder $coder = NULL, IClient $client = NULL)
	{
		if ($baseUrl !== NULL) {
			$baseUrl = (string) $baseUrl;
			if (!array_key_exists('scheme', Helpers::parseUrl($baseUrl))) {
				throw new LogicException("Base URL '$baseUrl' must be absolute.");
			}
		}

		$this->baseUrl = $baseUrl;
		$this->defaultHeaders = $defaultHeaders;
		$this->coder = $coder ?: new Coders\DefaultCoder;
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
	 * @return ICoder
	 */
	public function getCoder()
	{
		return $this->coder;
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
	 * @param  mixed
	 * @param  array
	 * @return Response
	 */
	public function patch($url, $body, array $headers = [])
	{
		return $this->process(
				$this->createRequest(Request::PATCH, $url, $headers, $body)
		);
	}


	/**
	 * @param  string
	 * @param  mixed
	 * @param  array
	 * @return Response
	 */
	public function post($url, $body, array $headers = [])
	{
		return $this->process(
				$this->createRequest(Request::POST, $url, $headers, $body)
		);
	}


	/**
	 * @param  string
	 * @param  mixed
	 * @param  array
	 * @return Response
	 */
	public function put($url, $body = '', array $headers = [])
	{
		return $this->process(
				$this->createRequest(Request::PUT, $url, $headers, $body)
		);
	}


	/**
	 * @param  string
	 * @param  string
	 * @param  array
	 * @param  mixed|NULL
	 * @return Request
	 */
	protected function createRequest($method, $url, array $headers, $body = NULL)
	{
		if ($this->baseUrl !== NULL) {
			$url = Helpers::absolutizeUrl($this->baseUrl, $url);
		}

		$request = new Request($method, $url, $headers, $body, $this->getCoder());
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
