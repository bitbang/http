<?php

namespace Bitbang\Http\Tests;


/**
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class SslWrapper
{
	/** @var resource[] */
	private $resources = [];

	/** @var string */
	private $address;

	/** @var int */
	private $port;


	/**
	 * @param  string  endpoint address
	 * @param  int  endpoint port
	 */
	public function __construct($address, $port)
	{
		$this->address = $address;
		$this->port = $port;
	}


	/**
	 * @param  string  listening address
	 * @param  int  listening port
	 * @param  string  path to PEM file
	 */
	public function listen($address, $port, $pemFile)
	{
		$context = stream_context_create([
			'ssl' => [
				'local_cert' => $pemFile,
			],
		]);

		$server = stream_socket_server("tcp://$address:$port", $errorNo, $errorStr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);
		stream_set_blocking($server, 1);
		stream_socket_enable_crypto($server, FALSE);
		stream_set_blocking($server, 0);

		do {
			$read = array_merge([$server], $this->resources);
			$write = $exceptions = NULL;

			stream_select($read, $write, $exceptions, NULL);
			foreach ($read as $resource) {
				if ($resource === $server) {
					$this->accept($server);
				} else {
					$this->forward($resource);
				}
			}
		} while (TRUE);
	}


	/**
	 * @param  resource
	 */
	private function accept($server)
	{
		$resource = stream_socket_accept($server);

		stream_set_blocking($resource, 1);
		$res = @stream_socket_enable_crypto($resource, TRUE, STREAM_CRYPTO_METHOD_TLS_SERVER);  // @ - e.g. when using HTTP instead of HTTPS
		if ($res === FALSE) {
			@stream_socket_shutdown($resource, STREAM_SHUT_RDWR);
			@fclose($resource);
			return;
		}
		stream_set_blocking($resource, 0);

		$endpoint = stream_socket_client("tcp://{$this->address}:{$this->port}");
		stream_set_blocking($endpoint, 0);

		$this->resources[(string) $resource] = $endpoint;
		$this->resources[(string) $endpoint] = $resource;
	}


	/**
	 * @param  resource
	 */
	private function forward($source)
	{
		$endpoint = $this->resources[(string) $source];

		$data = @fread($source, 4096);  // @ - SSL/TLS layer may emit warning

		stream_set_blocking($endpoint, 1); // @todo: Non-blocking write?
		fwrite($endpoint, $data);
		stream_set_blocking($endpoint, 0);

		if ($data == '' && feof($source)) {  // intentionally ==
			$this->close($source);
		}
	}


	/**
	 * @param  resource
	 */
	private function close($resource)
	{
		$endpoint = $this->resources[(string) $resource];
		unset(
			$this->resources[(string) $resource],
			$this->resources[(string) $endpoint]
		);

		@stream_set_blocking($endpoint, 1);
		@stream_set_blocking($resource, 1);

		@stream_socket_enable_crypto($endpoint, FALSE);
		@stream_socket_enable_crypto($resource, FALSE);

		@stream_socket_shutdown($endpoint, STREAM_SHUT_RDWR);
		@stream_socket_shutdown($resource, STREAM_SHUT_RDWR);

		@fclose($endpoint);
		@fclose($resource);
	}

}
