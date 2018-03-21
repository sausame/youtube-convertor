<?php

require('files.php');
require('rpc.php');

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

function renamePath($oldname, $newname) {

	syslog(LOG_WARNING, "$oldname --> $newname");
	rename($oldname, $newname);
}

function unlinkPath($filename, $iskept=false) {

	if ($iskept) {
		renamePath($filename, $filename.'.old');
	} else {
		syslog(LOG_WARNING, "Unlink $filename");
		unlink($filename);
	}
}

function translateToM4v($path, $videoFilename, &$retval, $framerate=null) {

	$pos = strrpos($videoFilename, '.');

	// XXX: Pos should be positive.
	$prefix = substr($videoFilename, 0, $pos);
	$filename = "$prefix.m4v";

	if (! empty($framerate)) {
		$framerate = "-r $framerate";
	}

	$cmd = "cd $path && ffmpeg -y -i '$videoFilename' $framerate -vcodec h264 '$filename'";
	runCommand($cmd, $retval);

	unlinkPath("$path/$videoFilename");

	return $filename;
}

function translateToM4a($path, $audioFilename, &$retval, $quality=null) {

	$pos = strrpos($audioFilename, '.');

	// XXX: Pos should be positive.
	$prefix = substr($audioFilename, 0, $pos);
	$filename = "$prefix.m4a";

	if (! empty($quality)) {
		$quality = "'-b:a' " . $quality . "k";
	}

	$cmd = "cd $path && ffmpeg -y -i '$audioFilename' -vn -acodec aac -strict -2 $quality '-bsf:a' aac_adtstoasc '$filename'";
	runCommand($cmd, $retval);

	unlinkPath("$path/$audioFilename");

	return $filename;
}

function merge($path, $videoFilename, $audioFilename, &$retval) {

	$pos = strrpos($videoFilename, '.');

	// XXX: Pos should be positive.
	$prefix = substr($videoFilename, 0, $pos);
	$filename = "$prefix.mp4";

	if ($filename == $videoFilename) {
		$videoFilename = "$prefix.m4v";
		rename("$path/$filename", "$path/$videoFilename");
	}

	$cmd = "cd $path && ffmpeg -y -i '$videoFilename' -i '$audioFilename' -c copy -map '0:v:0' -map '1:a:0' '$filename'";
	runCommand($cmd, $retval);

	unlinkPath("$path/$videoFilename");
	unlinkPath("$path/$audioFilename");

	return $filename;
}

class Downloader {

	private $envPath;
	private $savePath;
	private $url;

	public function __construct($configFile, $url) {

		$config = parse_ini_file($configFile);

		$this->envPath = $config['env-path'];
		$this->savePath = $config['save-path'];

		$this->url = $url;
	}

	public function getPath() {
		return $this->savePath;
	}

	public function download($format, $options, &$retval) {

		$cmd = "export LC_ALL=en_US.UTF-8 && export PATH=$this->envPath:\$PATH && youtube-dl -f $format $options -o '$this->savePath/%(id)s.%(ext)s' '$this->url'";
		runCommand($cmd, $retval);

		return getLastModifiedFile($this->savePath);
	}

	public static function isSucceeded($retval) {

		if (! $retval) {
			return false;
		}

		foreach ($retval as $value) {

			$pos = strpos($value, 'Destination:');

			if (false === $pos) {
				continue;
			}

			return true;
		}

		return false;
	}

	public function getResponse($succeeded, $filename, $type, $duration, $retval) {

		$log = implode('\n', $retval);
		$chars = array("\r", "\n", "\"");
		$log = str_replace($chars, '', $log);

		$data = "{\"originalurl\": \"$this->url\", \"type\": \"$type\", \"url\": \"$filename\", \"duration\": $duration, \"log\": \"$log\"}";

		if ($succeeded) {
			return Rpc::onSucceed($data);
		} else {
			return Rpc::onError($data);
		}
	}
}

openlog("Convertor", LOG_PID | LOG_PERROR, LOG_LOCAL0);

$downloader = new Downloader('config.ini', getParamOrExit('url'));

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
				$filename = translateToM4v($downloader->getPath(), $filename, $retval, $framerate);

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
				$filename = translateToM4a($downloader->getPath(), $filename, $retval, $quality);

				$retValue = array_merge($retValue, $retval);
			}

			$audioFilename = $filename;

			// Merge
			$filename = merge($downloader->getPath(), $videoFilename, $audioFilename, $retval);

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

echo($downloader->getResponse($succeeded, $filename, $type, $duration, $retValue));
?>

