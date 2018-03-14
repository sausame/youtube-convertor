<?php

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

$cmd = "export PATH=$envPath:\$PATH && youtube-dl -f $format $options '$url'";
echo(date('Y-m-d H:i:s'));
system($cmd, $retval);
echo(date('Y-m-d H:i:s'));
?>

