<?php

require('convertor.php');
require('downloader.php');

openlog("Convertor", LOG_PID | LOG_PERROR, LOG_LOCAL0);

$id = getParamOrExit('id');
$url = getParamOrExit('url');
$fulltitle = getParamOrExit('fulltitle');
$speed = getParamOrExit('speed');

$downloader = new Downloader('config.ini');
$downloader->setId($id);
$downloader->setUrl($url);
$downloader->setFulltitle($fulltitle);

$type = getParamOrExit('type');

$succeeded = true;

$format = '';
$options = '';
$filename = null;
$retValue = array();

$now = time();

if ('normal' == $type) {

	$type = 'video';
	$format = getParamOrExit('normal');

	$filename = $downloader->download($format, $options, $retValue);

	if (! Downloader::isSucceeded($retValue)) {
		$succeeded = false;
	}

} elseif ('video+audio' == $type) {

	$type = 'video';

	$formats = json_decode(file_get_contents('configs/formats.json'), true);

	$video = getParamOrExit('video');
	$vidoeExt = $formats[$video]['ext'];

	$audio = getParamOrExit('audio');
	$audioExt = $formats[$audio]['ext'];

	if ('m4v' == $vidoeExt and 'm4a' == $audioExt) {

		$format = "$video+$audio";
		$filename = $downloader->download($format, $options, $retValue);

	} else {

		do {

			// Video
			$filename = $downloader->download($video, $options, $retval);

			if (! Downloader::isSucceeded($retval)) {
				$succeeded = false;
				break;
			}

			$retValue = array_merge($retValue, $retval);

			// To m4v
			if ('m4v' != $vidoeExt) {

				$framerate = getParam('video-framerate');
				$filename = Convertor::translateToM4v($downloader->getPath(), $filename, $retval, $framerate);

				$retValue = array_merge($retValue, $retval);
			}

			$videoFilename = $filename;

			# Audio
			$options = "-x";

			$filename = $downloader->download($audio, $options, $retval);

			if (! Downloader::isSucceeded($retval)) {
				$succeeded = false;
				break;
			}

			$retValue = array_merge($retValue, $retval);

			// To m4a
			if ('m4a' != $audioExt) {

				$quality = getParam('audio-quality');
				$filename = Convertor::translateToM4a($downloader->getPath(), $filename, $retval, $quality);

				$retValue = array_merge($retValue, $retval);
			}

			$audioFilename = $filename;

			// Merge
			$filename = Convertor::merge($downloader->getPath(), $videoFilename, $audioFilename, $retval);

			$retValue = array_merge($retValue, $retval);

		} while (false);
	}
} elseif ('audio' == $type) {

	$format = getParamOrExit('audio');
	$quality = getParam('audio-quality');

	if (! empty($quality)) {
		$quality = "--audio-quality $quality";
	}

	$options = "-x --audio-format mp3 $quality";

	$filename = $downloader->download($format, $options, $retValue);

	if (! Downloader::isSucceeded($retValue)) {
		$succeeded = false;
	}

} else {
	http_response_code(302);
	exit();
}

$duration = time() - $now;

if ($succeeded) {
	Convertor::changeSpeed($downloader->getPath(), $filename, $speed);
}

$downloader->saveInformation($filename);

echo($downloader->getResponse($succeeded, $filename, $type, $duration, $retValue));
?>

