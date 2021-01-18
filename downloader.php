<?php

include_once('files.php');
include_once('rpc.php');
include_once('utils.php');

class Downloader {

	private $envPath;
	private $savePath;
	private $url;
	private $id;

	public function __construct($configFile, $id, $url, $fulltitle) {

		$config = parse_ini_file($configFile);

		$this->envPath = $config['env-path'];
		$this->savePath = $config['save-path'] . '/'. $id;

		if (!is_dir($this->savePath)) {
			mkdir($this->savePath, 0777);
		}

		$this->id = $id;
		$this->url = $url;
		$this->fulltitle = $fulltitle;
	}

	public function getPath() {
		return $this->savePath;
	}

	public function download($format, $options, &$retval) {

		$cmd = "export LC_ALL=en_US.UTF-8 && export PATH=$this->envPath:\$PATH && youtube-dl -f $format $options -o '$this->savePath/%(id)s.%(ext)s' --exec 'touch {}' '$this->url'";
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

		$data = "{\"url\": \"$this->url\", \"type\": \"$type\", \"id\": \"" . $this->id . "\", \"filename\": \"$filename\", \"duration\": $duration, \"log\": \"$log\"}";

		if ($succeeded) {
			return Rpc::onSucceed($data);
		} else {
			return Rpc::onError($data);
		}
	}

	public function saveInformation($filename) {

		if (! $fp = @fopen($this->savePath . "/information.json", 'wb')) {
			return false;
		}

		$size = filesize($this->savePath . "/" . $filename);

		$data = "{\"id\": \"" . $this->id . "\", \"url\": \"" . $this->url . "\", \"fulltitle\": \"". $this->fulltitle . "\", \"filename\": \"". $filename . "\", \"filesize\": ". $size . "}";

		fwrite($fp, $data);
		@fclose($fp);

		return true;
	}
}

?>

