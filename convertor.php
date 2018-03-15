<?php

require('files.php');
require('rpc.php');

function getParamOrExit($name) {

	$value = '';

	if (! empty($_POST)) {
		$value = $_POST[$name];
	} elseif (! empty($_GET)) {
		$value = $_GET[$name];
	}

	if ('' != $value) {
		return $value;
	}

	http_response_code(302);
	exit();
}

function isSucceeded($retval) {

	foreach ($retval as $value) {

		$pos = strpos($value, 'Destination:');

		if (false === $pos) {
			continue;
		}

		return true;
	}

	return false;
}

$url = getParamOrExit('url');
$type = getParamOrExit('type');
$format = '';
$options = '';

if ('normal' == $type) {

	$format = getParamOrExit('normal');

} elseif ('video+audio' == $type) {

	$video = getParamOrExit('video');
	$audio = getParamOrExit('audio');

	$format = "$video+$audio";

} elseif ('audio' == $type) {

	$format = getParamOrExit('audio');
	$quality = getParamOrExit('quality');

	$options = "-x --audio-format mp3 --audio-quality $quality";

} else {
	http_response_code(302);
	exit();
}

$config = parse_ini_file('config.ini');
$envPath = $config['env-path'];
$savePath = $config['save-path'];

$cmd = "export PATH=$envPath:\$PATH && youtube-dl -f $format $options -o '$savePath/%(title)s.%(ext)s' '$url'";

$now = time();
exec($cmd, $retval);
$duration = time() - $now;

$filename = getLastModifiedFile($savePath);
$log = implode("\n", $retval);

$data = "{\"duration\": $duration, \"url\": \"$filename\", \"log\": \"$log\"}";

if (isSucceeded($retval)) {
	echo(Rpc::onSucceed($data));
} else {
	echo(Rpc::onError($data));
}

?>

