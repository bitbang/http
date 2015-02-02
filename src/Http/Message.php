<?php

namespace Bitbang\Http;


/**
 * HTTP request or response ascendant.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
abstract class Message extends Sanity
{
	/** @var array[name => value] */
	private $headers = [];

	/** @var string|NULL */
	private $content;


	/**
	 * @param  array
	 * @param  string|NULL
	 */
	public function __construct(array $headers = [], $content = NULL)
	{
		foreach ($headers as $name => $values) {
			$values = (array) $values;
			if (count($values)) {
				$this->headers[strtolower($name)] = array_values($values);
			}
		}
		$this->content = $content;
	}


	/**
	 * @param  string
	 * @return bool
	 */
	public function hasHeader($name)
	{
		return array_key_exists(strtolower($name), $this->headers);
	}


	/**
	 * @param  string
	 * @return bool
	 */
	public function hasMultiHeader($name)
	{
		$name = strtolower($name);
		return array_key_exists($name, $this->headers) && count($this->headers[$name]) > 1;
	}


	/**
	 * @param  string
	 * @param  mixed
	 * @return mixed
	 */
	public function getHeader($name, $default = NULL)
	{
		$name = strtolower($name);
		return array_key_exists($name, $this->headers)
			? end($this->headers[$name])
			: $default;
	}


	/**
	 * @param  string
	 * @return mixed[]
	 */
	public function getMultiHeader($name, array $defaults = [])
	{
		$name = strtolower($name);
		return array_key_exists($name, $this->headers)
			? $this->headers[$name]
			: $defaults;
	}


	/**
	 * @param  string
	 * @param  string
	 * @return self
	 */
	protected function addHeader($name, $value)
	{
		$name = strtolower($name);
		if (!array_key_exists($name, $this->headers) && $value !== NULL) {
			$this->headers[$name] = [$value];
		}

		return $this;
	}


	/**
	 * @param  string
	 * @param  string|string[]
	 * @param  bool
	 * @return self
	 */
	protected function addMultiHeader($name, $value, $append = TRUE)
	{
		$name = strtolower($name);
		$value = (array) $value;
		$exists = array_key_exists($name, $this->headers);

		if ($append || (!$exists && count($value))) {
			$this->headers[$name] = $exists
				? array_merge($this->headers[$name], array_values($value))
				: array_values($value);
		}

		return $this;
	}


	/**
	 * @param  string
	 * @param  string|NULL
	 * @return self
	 */
	protected function setHeader($name, $value)
	{
		$name = strtolower($name);
		if ($value === NULL) {
			unset($this->headers[$name]);
		} else {
			$this->headers[$name] = [$value];
		}

		return $this;
	}


	/**
	 * @param  string
	 * @param  string[]
	 * @return self
	 */
	protected function setMultiHeader($name, array $value)
	{
		$name = strtolower($name);
		if (count($value) < 1) {
			unset($this->headers[$name]);
		} else {
			$this->headers[$name] = array_values($value);
		}

		return $this;
	}


	/**
	 * @return array
	 */
	public function getHeaders()
	{
		return array_map('end', $this->headers);
	}


	/**
	 * @return array[]
	 */
	public function getMultiHeaders()
	{
		return $this->headers;
	}


	/**
	 * @return string|NULL
	 */
	public function getContent()
	{
		return $this->content;
	}

}
