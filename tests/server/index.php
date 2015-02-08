<?php

if (function_exists('xdebug_disable')) {
	xdebug_disable();
}

$requestUri = $_SERVER['REQUEST_URI'];

if ($requestUri === '/ping') {
	echo 'pong';

} elseif ($requestUri === '/200') {
	header('HTTP/1.1 200');
	echo "The 200 response.";

} elseif ($requestUri === '/404') {
	header('HTTP/1.1 404');
	echo "The 404 response.";

} elseif ($requestUri === '/receive-headers') {
	header('X-Powered-By: bitbang/http.tests');

} elseif ($requestUri === '/send-headers') {
	header('X-Bar: bar-' . $_SERVER['HTTP_X_FOO']);

} elseif ($requestUri === '/receive-multiple-line-header') {
	header("X-Bar: a\n b\n\tc");

} elseif (preg_match('~^/redirect/([0-9]{3})$~', $requestUri, $m)) {
	header("Location: http://$_SERVER[HTTP_HOST]/redirected", TRUE, (int) $m[1]);
	echo 'Redirection made';

} elseif ($requestUri === '/redirected') {
	echo 'Redirection finished';

} elseif (preg_match('~^/redirect-loop(?:/(\d+))?$~', $requestUri, $m)) {
	$count = empty($m[1]) ? 1 : (int) $m[1];
	if ($count >= $_SERVER['HTTP_X_MAX_LOOP_COUNT']) {
		header("Location: http://$_SERVER[HTTP_HOST]/redirected");
	} else {
		header("Location: http://$_SERVER[HTTP_HOST]/redirect-loop/" . ($count + 1));
	}
	echo 'Redirection loop';

} else {
	header("HTTP/1.1 500");
	echo "Missing request handler for '$requestUri'.\n";
	exit(255);
}
