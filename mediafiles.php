<?php

require('files.php');

$config = parse_ini_file('config.ini');
$savePath = $config['save-path'];

$files = scanFolder($savePath, false, true);
$count = sizeof($files);
$num = 0;

echo('{"files": [');

for ($i = 0; $i < $count; $i ++) {

	$path = $savePath . '/' . $files[$i] . '/information.json';
	$content = file_get_contents($path);
	$content = trim($content);

	if ('' === $content) {
		continue;
	}

	if ($num > 0) {
		echo(', ');
	}

	$num ++;

	echo(file_get_contents($path));
}

echo('], "num": '.$num.'}');

?>

