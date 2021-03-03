<?php

include_once('files.php');
include_once('rpc.php');
include_once('utils.php');
include_once('network.php');

class Downloader {

	private $envPath;
	private $saveDir;
	private $savePath;
	private $url;
	private $id;

	public function __construct($configFile) {

		$config = parse_ini_file($configFile);
		$this->envPath = $config['env-path'];
		$this->saveDir = $config['save-path'];
	}

	public function setId($id) {
		$this->savePath = $this->saveDir . '/'. $id;

		if (!is_dir($this->savePath)) {
			mkdir($this->savePath, 0777);
		}

		$this->id = $id;
	}

	public function setUrl($url) {
		$this->url = $url;
	}

	public function setFulltitle($fulltitle) {
		$this->fulltitle = $fulltitle;
	}

	public function getPath() {
		return $this->savePath;
	}

	private static function dumpFormat($format, $data) {

		$filesize = $data["filesize"];

		if (! $filesize) {
			$url = $data["url"];
			if ($url) {
				$filesize = remotefileSize($url);
			} else {
				$filesize = -1;
			}
		}

		echo(" {\n");
		echo("\t\t\t\"format_id\": \"".$data["format_id"]."\",\n");
		echo("\t\t\t\"format\": \"".$format["format"]."\",\n");
		echo("\t\t\t\"codec\": \"".$format["codec"]."\",\n");
		echo("\t\t\t\"ext\": \"".$format["ext"]."\",\n");
		echo("\t\t\t\"type\": \"".$format["type"]."\",\n");
		echo("\t\t\t\"filesize\": $filesize\n");
		echo("\t\t}");
	}

	private static function dumpGroup($obj, $formats, $type) {

		echo("\t\"$type\": [");
		$first = true;

		foreach ($obj['formats'] as $data) {

			$formatId = (int)$data['format_id'];

			foreach ($formats as $key => $format) {

				if ($formatId == (int)$key) {

					if ($type == $format['type']) {

						if ($first) {
							$first = false;
						} else {
							echo(",");
						}

						Downloader::dumpFormat($format, $data);
					}

					break;
				}
			}
		}
		echo("]");
	}

	private function dump($content) {

		$formats = json_decode(file_get_contents('configs/formats.json'), true);

		$obj = json_decode($content, true);

		$abr = $obj["abr"];
 		if (! $abr) {
 			$abr = 320;
 		}

		$imgUrl = $obj["thumbnail"];
		if ($imgUrl) {
			$pos = strrpos($imgUrl, '/');
			if ($pos) {
				$filename = substr($imgUrl, $pos + 1);
				$savePath = $this->saveDir . '/' . $filename;

				saveRemoteFile($savePath, $imgUrl);
				$imgUrl = 'files/' . $filename;
			}
		}

		$fulltitle = $obj["fulltitle"];

		if ($fulltitle) {
			$fulltitle = str_replace("\"", "'", $fulltitle);
		}

		echo("{\n");
		echo("\t\"id\": \"".$obj["id"]."\",\n");
		echo("\t\"webpage_url\": \"".$obj["webpage_url"]."\",\n");
		echo("\t\"fulltitle\": \"".$fulltitle."\",\n");
		echo("\t\"thumbnail\": \"".$imgUrl."\",\n");
		echo("\t\"duration\": ".$obj["duration"].",\n");
		echo("\t\"abr\": ".$abr.",\n");
		echo("\t\"fps\": ".$obj["fps"].",\n");
		Downloader::dumpGroup($obj, $formats, 'audio');
		echo(",\n");
		Downloader::dumpGroup($obj, $formats, 'video');
		echo(",\n");
		Downloader::dumpGroup($obj, $formats, 'normal');
		echo("\n}\n");
	}

	public function dumpInfor() {

		$cmd = "export LC_ALL=en_US.UTF-8 && export PATH=$this->envPath:\$PATH && youtube-dl -j $this->url";
		$content = exec($cmd, $retval);

		if ($content) {
			$this->dump($content);
		}
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

