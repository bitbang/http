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
	header('X-Multi: one');
	header('X-Multi: two', FALSE);

} elseif ($requestUri === '/send-headers') {
	header('X-Bar: bar-' . $_SERVER['HTTP_X_FOO']);

//} elseif ($requestUri === '/receive-multiple-line-header') {
//	# Somewhere between PHP 5.5 and 5.6, PHP stopped setting such header. Don't know how to test it.
//	header("X-Bar: a\n b\n\tc");

} elseif (preg_match('~^/redirect/([0-9]{3})$~', $requestUri, $m)) {
	header("Location: http://$_SERVER[HTTP_HOST]/redirected", TRUE, (int)$m[1]);
	echo 'Redirection made';

} elseif ($requestUri === '/relative-redirect') {
	header("Location: /redirected", TRUE, 301);
	echo 'Redirection made';

} elseif ($requestUri === '/redirected') {
	echo 'Redirection finished';

} elseif (preg_match('~^/redirect-loop(?:/(\d+))?$~', $requestUri, $m)) {
	$count = empty($m[1]) ? 1 : (int)$m[1];
	if ($count >= $_SERVER['HTTP_X_MAX_LOOP_COUNT']) {
		header("Location: http://$_SERVER[HTTP_HOST]/redirected");
	} else {
		header("Location: http://$_SERVER[HTTP_HOST]/redirect-loop/" . ($count + 1));
	}
	echo 'Redirection loop';

} elseif ($requestUri === '/user-agent') {
	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		header("X-User-Agent: $_SERVER[HTTP_USER_AGENT]");
	}

} elseif ($requestUri === '/method') {
	echo "method-$_SERVER[REQUEST_METHOD]";

} elseif ($requestUri === '/body') {
	echo 'raw-' . file_get_contents('php://input');

} else {
	header('HTTP/1.1 500');
	echo $message = "Missing request handler for '$requestUri' . \n";
	file_put_contents('php://stderr', $message);
	exit(255);
}
