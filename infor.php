<?php

	require('network.php');

	function dumpFormat($format, $data) {

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

	function dumpGroup($obj, $formats, $type) {

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

						dumpFormat($format, $data);
					}

					break;
				}
			}
		}
		echo("]");
	}

	function dump($content) {

		$formats = json_decode(file_get_contents('configs/formats.json'), true);

		$obj = json_decode($content, true);

		echo("{\n");
		echo("\t\"id\": \"".$obj["id"]."\",\n");
		echo("\t\"webpage_url\": \"".$obj["webpage_url"]."\",\n");
		echo("\t\"fulltitle\": \"".$obj["fulltitle"]."\",\n");
		echo("\t\"thumbnail\": \"".$obj["thumbnail"]."\",\n");
		echo("\t\"duration\": ".$obj["duration"].",\n");
		echo("\t\"abr\": ".$obj["abr"].",\n");
		echo("\t\"fps\": ".$obj["fps"].",\n");
		dumpGroup($obj, $formats, 'audio');
		echo(",\n");
		dumpGroup($obj, $formats, 'video');
		echo(",\n");
		dumpGroup($obj, $formats, 'normal');
		echo("\n}\n");
	}

	function getInfor($url) {

		$config = parse_ini_file('config.ini');
		$envPath = $config['env-path'];

		$cmd = "export PATH=$envPath".':$PATH && youtube-dl -j "' . $url . '"';
		$output = exec($cmd, $retval);

		return $output;
	}

	function dumpInfor($url) {
		$content = getInfor($url);

		if ($content) {
			dump($content);
		}
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

