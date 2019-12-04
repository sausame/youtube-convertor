<?php

require('files.php');

function deleteMedia($id) {

	$config = parse_ini_file('config.ini');
	$savePath = $config['save-path'] . '/'. $id;

	try {
		deleteDir($savePath);
		return 1;
	} catch (Exception $e){
		syslog(LOG_ERROR, "Error:".$e->getMessage());
		return 0;
	}
}

function executeAction($action, $id) {

	if (strcasecmp('delete', $action) == 0) {
		return deleteMedia($id);
	} else if (strcasecmp('reload', $action) == 0) {
		return 0;
	} else {
		return 0;
	}
}

$action = NULL;

if (! empty($_POST)) {
	$action = $_POST['action'];
} elseif (! empty($_GET)) {
	$action = $_GET['action'];
}

$id = NULL;

if (! empty($_POST)) {
	$id = $_POST['id'];
} elseif (! empty($_GET)) {
	$id = $_GET['id'];
}

if ('' != $action && '' != $id) {
	$num = executeAction($action, $id);
	echo('{"num": '.$num.'}');
} else {
	http_response_code(302);
}

?>

