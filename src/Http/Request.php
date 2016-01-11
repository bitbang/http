<?php

namespace Bitbang\Http;


/**
 * HTTP request envelope.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class Request extends Message
{
	/** HTTP request method */
	const
		DELETE = 'DELETE',
		GET = 'GET',
		HEAD = 'HEAD',
		PATCH = 'PATCH',
		POST = 'POST',
		PUT = 'PUT';


	/** @var string */
	private $method;

	/** @var string */
	private $url;


	/**
	 * @param  string $method
	 * @param  string $url
	 * @param  array $headers
	 * @param  string|NULL $body
	 * @param  IDecoder $decoder
	 */
	public function __construct($method, $url, array $headers = [], $body = NULL, IDecoder $decoder = NULL)
	{
		$this->method = $method;
		$this->url = $url;
		parent::__construct($headers, $body, $decoder);
	}


	/**
	 * @param  string
	 * @return bool
	 */
	public function isMethod($method)
	{
		return strcasecmp($this->method, $method) === 0;
	}


	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}


	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}


	/**
	 * @param  string
	 * @param  string
	 * @return self
	 */
	public function addHeader($name, $value)
	{
		return parent::addHeader($name, $value);
	}


	/**
	 * @param  string
	 * @param  string|string[]
	 * @return self
	 */
	public function addMultiHeader($name, $value)
	{
		return parent::addMultiHeader($name, $value);
	}


	/**
	 * @param  string
	 * @param  string|NULL
	 * @return self
	 */
	public function setHeader($name, $value)
	{
		return parent::setHeader($name, $value);
	}


	/**
	 * @param  string
	 * @param  string[]
	 * @return self
	 */
	public function setMultiHeader($name, array $value)
	{
		return parent::setMultiHeader($name, $value);
	}

}
