<?php

include_once('files.php');
include_once('utils.php');

class Convertor {

	public static function getVideoFrameRate($path, $filename) {

		$cmd = "cd $path && ffprobe -show_streams '$filename'";
		runCommand($cmd, $retval);

		$output = implode("\n", $retval);

		$config = parse_ini_string($output);
		$framerate = $config['r_frame_rate'];

		$pos = strpos($framerate, '/');
		$framerate = substr($framerate, 0, $pos);

		if (empty($framerate)) {
			return 0;
		}

		return (int)$framerate;
	}

	public static function translateToM4v($path, $videoFilename, &$retval, $framerate=null) {

		$pos = strrpos($videoFilename, '.');

		// XXX: Pos should be positive.
		$prefix = substr($videoFilename, 0, $pos);
		$filename = "$prefix.m4v";

		if (! empty($framerate)) {
			if ((int)$framerate < Convertor::getVideoFrameRate($path, $videoFilename)) {
				$framerate = "-r $framerate";
			} else {
				$framerate = null;
			}
		}

		$cmd = "cd $path && ffmpeg -y -i '$videoFilename' $framerate -vcodec h264 '$filename'";
		runCommand($cmd, $retval);

		unlinkPath("$path/$videoFilename");

		return $filename;
	}

	public static function translateToM4a($path, $audioFilename, &$retval, $quality=null) {

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

	public static function merge($path, $videoFilename, $audioFilename, &$retval) {

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

	public static function changeSpeed($path, $filename, $speed) {

		if (0 == strcasecmp('1', $speed)) {
			return;
		}

		$start = strrpos($filename, '.');

		if (! $start) {
			return;
		}

		$tempFilename = "temp-$filename";

		$suffix = substr($filename, $start + 1);
		if (0 == strcasecmp('mp3', $suffix)) {
			$cmd = "cd $path && ffmpeg -y -i $filename -filter:a \"atempo=$speed\" $tempFilename";
		} else if (0 == strcasecmp('mp4', $suffix)) {
			$videoSpeed = number_format(1/(float)$speed, 2, '.', '');
			$cmd = "cd $path && ffmpeg -y -i $filename -vf \"setpts=$videoSpeed*PTS\" -filter:a \"atempo=$speed\" $tempFilename";
		} else {
			return;
		}

		$cmd = "cd $path && $cmd && mv $tempFilename $filename";
		runCommand($cmd, $retval);
	}

	public static function convertMediafile($srcFilename, $destFilename) {

		$cmd = "ffmpeg -y -i '$srcFilename' '$destFilename'";
		runCommand($cmd, $retval);

		unlinkPath("$srcFilename");

		return $destFilename;
	}

}

?>

