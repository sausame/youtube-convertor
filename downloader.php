<?php

include_once('files.php');
include_once('rpc.php');
include_once('utils.php');

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

?>

