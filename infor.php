<?php

	require('downloader.php');

	function dumpInfor($url) {

		$downloader = new Downloader('config.ini');
		$downloader->setUrl($url);
		$downloader->dumpInfor();
	}
?>

<?php

$url = NULL;

if (! empty($_POST)) {
	$url = $_POST['url'];
} elseif (! empty($_GET)) {
	$url = $_GET['url'];
}

if ('' != $url) {
	dumpInfor($url);
} else {
	http_response_code(302);
}
?>

