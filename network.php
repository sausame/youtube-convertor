<?php
/**
* Remote File Size Using cURL
* @param srting $url
* @return int || void
*/
function remotefileSize($url) {

	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_NOBODY, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
	curl_exec($ch);

	$filesize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

	curl_close($ch);

	if ($filesize) return $filesize;

	return -1;
}

function saveRemoteFile($filename, $url) {

	$ch = curl_init($url); 

	curl_setopt($ch, CURLOPT_HEADER, 0); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 3);

	$data = curl_exec($ch); 

	curl_close($ch); 

	if (! $fp = @fopen($filename, 'wb')) {
		return false;
	}

	fwrite($fp, $data);
	@fclose($fp);

	return true;
}

?>

