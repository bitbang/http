<?php

namespace Bitbang\Http\Clients;

use Bitbang\Http;


/**
 * Client which use the file_get_contents() with a HTTP context options.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class StreamClient extends AbstractClient
{
	/** @var array */
	private $options = [];

	/** @var callable|NULL */
	private $onContextCreate;


	/**
	 * @param  array|callable function(resource $context, string $url)
	 */
	public function __construct($optionsOrCallback = NULL)
	{
		if (is_array($optionsOrCallback) && array_keys($optionsOrCallback) !== [0, 1]) {
			$this->options = $optionsOrCallback;
		} else {
			$this->onContextCreate = $optionsOrCallback;
		}
	}


	protected function setupRequest(Http\Request $request)
	{
		parent::setupRequest($request);
		$request->setHeader('Connection', 'close');
		$request->addHeader('User-Agent', 'Bitbang/' . Http\Library::VERSION . ' (Stream)');
	}


	/**
	 * @return Http\Response
	 *
	 * @throws Http\BadResponseException
	 */
	protected function processRequest(Http\Request $request)
	{
		$headerStr = [];
		foreach ($request->getHeaders() as $name => $value) {
			foreach ((array) $value as $v) {
				$headerStr[] = "$name: $v";
			}
		}

		$options = [
			'http' => [
				'method' => $request->getMethod(),
				'header' => implode("\r\n", $headerStr) . "\r\n",
				'follow_location' => 0,  # Handled manually
				'protocol_version' => 1.1,
				'ignore_errors' => TRUE,
			],
			'ssl' => [
				'verify_peer' => TRUE,
				'disable_compression' => TRUE,  # Effective since PHP 5.4.13
				'SNI_enabled' => TRUE,

				/** @see https://wiki.mozilla.org/Security/Server_Side_TLS#Recommended_Ciphersuite */
				'ciphers' => 'ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:'
					. 'ECDHE-ECDSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-DSS-AES128-GCM-SHA256:kEDH+AESGCM:'
					. 'ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES128-SHA:'
					. 'ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA:ECDHE-ECDSA-AES256-SHA:'
					. 'DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA256:DHE-RSA-AES256-SHA256:'
					. 'DHE-DSS-AES256-SHA:DHE-RSA-AES256-SHA:!aNULL:!eNULL:!EXPORT:!DES:!RC4:!3DES:!MD5:!PSK',
			],
		];

		if (($body = $request->getBody()) !== NULL) {
			$options['http']['content'] = $body;
		}

		$options = array_replace_recursive($options, $this->options);

		list($code, $headers, $body) = $this->fileGetContents($request->getUrl(), $options);
		return new Http\Response($code, $headers, $body, $request->getDecoder());
	}


	/**
	 * @param  string
	 * @param  array
	 * @return array
	 *
	 * @throws Http\BadResponseException
	 */
	private function fileGetContents($url, array $contextOptions)
	{
		$context = stream_context_create($contextOptions);
		$this->onContextCreate && call_user_func($this->onContextCreate, $context, $url);

		$e = NULL;
		set_error_handler(function($severity, $message, $file, $line) use (& $e) {
			$e = new \ErrorException($message, 0, $severity, $file, $line, $e);
		}, E_WARNING);

		$content = file_get_contents($url, FALSE, $context);
		restore_error_handler();

		if (!isset($http_response_header)) {
			throw new Http\BadResponseException('Missing HTTP headers, request failed.', 0, $e);
		}

		if (!isset($http_response_header[0]) || !preg_match('~^HTTP/1[.]. (\d{3})~i', $http_response_header[0], $m)) {
			throw new Http\BadResponseException('HTTP status code is missing.', 0, $e);
		}
		unset($http_response_header[0]);

		$headers = [];
		foreach ($http_response_header as $header) {
			if (in_array(substr($header, 0, 1), [' ', "\t"], TRUE)) {
				$last .= ' ' . trim($header);  # RFC2616, 2.2

			} else {
				list($name, $value) = explode(':', $header, 2) + [NULL, NULL];
				$key = trim($name);
				$headers[$key][] = trim($value);
				$last = & $headers[$key][count($headers[$key]) - 1];
			}
		}

		return [$m[1], $headers, $content];
	}

}
