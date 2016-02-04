<?php

namespace Bitbang\Http;


/**
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class Helpers
{

	/**
	 * Replacement for PHP parse_url() function. Value of parsed parts is not validated.
	 *
	 * @param  string
	 * @return array  with some of: scheme, authority, user, pass, host, port, path, query, fragment
	 *
	 * @todo  Some smart regexp would be nice.
	 */
	public static function parseUrl($url)
	{
		$url = (string) $url;
		if ($url === '') {
			return ['path' => ''];
		}

		$parsed = [];

		list($url, $fragment) = explode('#', $url, 2) + [NULL, NULL];
		list($url, $query) = explode('?', $url, 2) + [NULL, NULL];

		if (preg_match('#^(?:([a-z][a-z0-9.+-]*):)?//([^/]+)(.*?)$#i', $url, $m)) {
			if ($m[1] !== '') {
				$parsed['scheme'] = $m[1];
			}

			$parsed['authority'] = $authority = $m[2];

			$parts = explode('@', $authority, 2);
			if (count($parts) === 2) {
				list($user, $pass) = explode(':', $parts[0]) + [NULL, NULL];
				$parsed['user'] = $user;
				if (isset($pass)) {
					$parsed['pass'] = $pass;
				}

				$authority = $parts[1];
			}

			list($host, $port) = explode(':', $authority, 2) + [NULL, NULL];
			$parsed['host'] = $host;
			if ($port !== NULL) {
				$parsed['port'] = $port;
			}

			if ($m[3] !== '') {
				$parsed['path'] = $m[3];
			}

		} elseif ($url !== '') {
			$parsed['path'] = $url;
		}

		if (isset($query)) {
			$parsed['query'] = $query;
		}
		if (isset($fragment)) {
			$parsed['fragment'] = $fragment;
		}

		return $parsed;
	}


	/**
	 * Creates absolute URL from $relative.
	 *
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function absolutizeUrl($absolute, $relative)
	{
		$parts = self::parseUrl($relative);
		if (isset($parts['scheme'])) {
			return $relative;
		}

		$absolute = self::parseUrl($absolute);
		$url = $absolute['scheme'] . ':';

		if (isset($parts['authority'])) {
			return $url . $relative;
		}
		$url .= '//' . $absolute['authority'];

		if (isset($parts['path']) && $parts['path'] !== '') {
			if ($parts['path'][0] === '/') {
				return $url . $parts['path'];
			} elseif (isset($absolute['path'])) {
				return $url . substr($absolute['path'], 0, strrpos($absolute['path'], '/')) . '/' . $relative;
			}

			return $url . '/' . $relative;
		}
		$url .= isset($absolute['path']) ? $absolute['path'] : '';

		if (isset($parts['query'])) {
			return $url . $relative;
		}
		$url .= isset($absolute['query']) ? "?$absolute[query]" : '';

		if (isset($parts['fragment'])) {
			return $url . $relative;
		}

		return $url . (isset($absolute['fragment']) ? "#$absolute[fragment]" : '');
	}

}
