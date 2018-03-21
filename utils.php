<?php

function getParam($name) {

	$value = '';

	if (! empty($_POST)) {
		$value = $_POST[$name];
	} elseif (! empty($_GET)) {
		$value = $_GET[$name];
	}

	return $value;
}

function getParamOrExit($name) {

	$value = getParam($name);

	if (! empty($value)) {
		return $value;
	}

	http_response_code(302);
	exit();
}

function runCommand($cmd, &$retval) {

	syslog(LOG_WARNING, $cmd);
	$retval = array();
	exec($cmd, $retval);
	$log = implode("\n", $retval);
	syslog(LOG_WARNING, $log);
}

?>

