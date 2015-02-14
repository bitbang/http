<?php

use Bitbang\Http\Tests\SslWrapper;

require __DIR__ . '/SslWrapper.php';

set_time_limit(0);

$config = parse_ini_file($configFile = __DIR__ . '/../server.ini', TRUE)['listen'];

$wrapper = new SslWrapper($config['address'], $config['port']);
$wrapper->listen($config['address'], $config['port_ssl'], dirname(realpath($configFile)) . '/' . $config['server_pem']);
